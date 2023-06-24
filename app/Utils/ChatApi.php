<?php


namespace App\Utils;

use App\Exceptions\ApiException;
use App\Helpers\HttpHelper;
use App\Models\chat\MjGptLog;
use App\Services\Api\ChatGptService;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ChatApi
{
    use HttpHelper;

    protected string $apiUrl;


    public function __construct(string $apiUrl, string $token)
    {
        $this->apiUrl = $apiUrl . '?token=' . $token;
    }

    /**
     * User: Yan
     * DateTime: 2023/5/14
     * @param $question
     * @param null $conversation_id
     * 发送问题GPT
     * @param int $task_id
     * @param int $function_id
     * @throws ApiException
     */
    public function sendGPT($data, $conversation_id, int $task_id, int $function_id)
    {
        try {
            $params['question'] = $data['question'];
            $params['tools'] = $data['tools'];
            $params['temperature'] = $data['temperature'];
            $params['replace'] = $data['replace'];
            if ($conversation_id) {
                $params['conversation_id'] = $conversation_id;
                $params['stateful'] = true;
            }
            $client = new Client([
                RequestOptions::VERIFY => public_path('cacert-2023-01-10.pem'),
                'stream'               => true
            ]);
            # 生成随机数8位
            $sendBoc = rand(10000000, 99999999);
            $buffer = '';
            $response = new StreamedResponse(function () use ($client, $params, $sendBoc, $task_id, $function_id, $buffer) {
                $headers = [
                    'accept'       => 'application/x-ndjson',
                    'content-type' => 'application/json; charset=utf-8'
                ];
                $request = $client->post($this->apiUrl, [
                    'headers' => $headers,
                    'body'    => json_encode($params)
                ]);

                // Send the beginning of the SSE stream
                echo "event: start\n";
                echo "data: {\n";
                echo "data: \"status\": \"ok\",\n";
                echo "data: \"message\": \"Stream started\",\n";
                echo "data: \"timestamp\": " . time() . ",\n";
                echo "data: \"task_id\": " . $task_id . "\n";
                echo "data: }\n";
                echo "\n";
                // Flush the output buffers
                ob_flush();
                flush();
                $stream = $request->getBody();
                $chunk = $stream->read(2048);

                $buffer .= $chunk;
                // Read the response body in chunks and send them as SSE events
                while (!$stream->eof()) {

                    $pos = strpos($buffer, "\n");
                        $line = substr($buffer, 0, $pos);
                        $buffer = substr($buffer, $pos + 1);
                        \Log::info('结束问题', [$this->apiUrl, $line]);
                        $lines = explode("\n", trim($line));

                        foreach ($lines as $value) {
                            $json = json_decode($value, true);
                            if ($json) {
                                # 存redis 用于下次请求
                                echo "data: " . json_encode($json) . "\n\n";
                                ob_flush();
                                flush();
                                Redis::setEx('data_' . $sendBoc, 60 * 60, json_encode($json));

                            }
                        }

                }
                echo "event: end\n";
                echo "data: {\n";
                echo "data: \"status\": \"ok\",\n";
                echo "data: \"message\": \"Stream ended\",\n";
                echo "data: \"timestamp\": " . time() . ",\n";
                echo "data: \"task_id\": " . $task_id . "\n";
                echo "data: }\n";
                echo "\n";
                ob_flush();
                flush();
                # 结算token
                $model = new ChatGptService();
                $model->settlement($sendBoc, $task_id, $function_id);
            });
            $response->headers->set('Content-Type', 'text/event-stream');
            $response->headers->set('X-Accel-Buffering', 'no');
            $response->headers->set('Cache-Control', 'no-cache');

            $response->send();
            return;
        } catch (Exception  $e) {
            if ($e instanceof RequestException && $e->hasResponse()) {
                $response = $e->getResponse();
                $body = $response->getBody();
                throw new ApiException($body);
            } else {
                throw new ApiException($e->getMessage());
            }
        }
    }

    /**
     * User: Yan
     * DateTime: 2023/5/14
     * 初始化MjGPT询问模式
     * @throws ApiException
     */
    public function MjSendGPTInit($mj_task_id, $user_id)
    {
        try {
            # 读取文件
            $txt = file_get_contents(config_path('MjInit.txt'));
            $headers = [
                'accept'       => 'application/json',
                'content-type' => 'application/json; charset=utf-8'
            ];
            $client = new Client([
                RequestOptions::VERIFY => public_path('cacert-2023-01-10.pem'),
            ]);
            $start = microtime(true);
            # 计算执行时间
            $request = $client->post($this->apiUrl, [
                'headers' => $headers,
                'body'    => json_encode([
                    'question'    => $txt,
                    'tools'       => false,
                    'temperature' => 0.6,
                    'replace'     => false,
                    'stateful'    => true,
                ])
            ]);
            $end = microtime(true);
            $executionTime = $end - $start;
            \Log::info('初始化MjGPT询问模式GPT请求时间', [$this->apiUrl, $executionTime]);
            $response = $request->getBody()->getContents();
            $response = json_decode($response, true);
            \Log::info('初始化MjGPT询问模式GPT返回', [$this->apiUrl, $response]);
            if ($response) {
                if (!empty($response['code']) && $response['code'] == 'api_error') {
                    throw new ApiException($response['detail']);
                }
                $res = MjGptLog::query()->create([
                    'mj_task_id' => $mj_task_id,
                    'user_id'    => $user_id,
                    'content'    => $response['answer'],
                    'type'       => 3,
                    'task_id'    => $response['conversation_id']
                ]);
                if ($res) {
                    return $res;
                }
            }
            return false;
        } catch (Exception|GuzzleException  $e) {
            if ($e instanceof RequestException && $e->hasResponse()) {
                $response = $e->getResponse();
                $body = $response->getBody();
                throw new ApiException($body);
            } else {
                throw new ApiException($e->getMessage());
            }
        }
    }

    public function mjSendGPT($question, $conversation_id, int $task_id, int $function_id)
    {
        try {
            $params['question'] = $question;
            $params['tools'] = false;
            $params['temperature'] = 0.6;
            $params['replace'] = false;
            $params['stateful'] = false;
            if ($conversation_id) {
                $params['conversation_id'] = $conversation_id;
                $params['stateful'] = true;
            }
            $client = new Client([
                RequestOptions::VERIFY => public_path('cacert-2023-01-10.pem'),
                'stream'               => true
            ]);
            # 生成随机数8位
            $sendBoc = rand(10000000, 99999999);
            $buffer = '';
            $response = new StreamedResponse(function () use ($client, $params, $sendBoc, $task_id, $function_id,$buffer) {
                $headers = [
                    'accept'       => 'application/x-ndjson',
                    'content-type' => 'application/json; charset=utf-8'
                ];
                $request = $client->post($this->apiUrl, [
                    'headers' => $headers,
                    'body'    => json_encode($params)
                ]);
                // Send the beginning of the SSE stream
                echo "event: start\n";
                echo "data: {\n";
                echo "data: \"status\": \"ok\",\n";
                echo "data: \"message\": \"Stream started\",\n";
                echo "data: \"timestamp\": " . time() . ",\n";
                echo "data: \"task_id\": " . $task_id . "\n";
                echo "data: }\n";
                echo "\n";
                // Flush the output buffers
                ob_flush();
                flush();
                $stream = $request->getBody();

                // Read the response body in chunks and send them as SSE events
                while ($pos = strpos($buffer, "\n")!==false) {
                    $chunk = $stream->read(4089);

                    $buffer .= $chunk;
                    $line = substr($buffer, 0, $pos);
                    $buffer = substr($buffer, $pos + 1);
                    $lines = explode("\n", trim($line));

                    foreach ($line as $value) {
                        $json = json_decode($value, true);
                        if ($json) {
                            # 存redis 用于下次请求
                            echo "data: " . json_encode($json) . "\n\n";
                            ob_flush();
                            flush();
                            Redis::setEx('data_' . $sendBoc, 60 * 60, json_encode($json));

                        }
                    }

                }
                echo "event: end\n";
                echo "data: {\n";
                echo "data: \"status\": \"ok\",\n";
                echo "data: \"message\": \"Stream ended\",\n";
                echo "data: \"timestamp\": " . time() . ",\n";
                echo "data: \"task_id\": " . $task_id . "\n";
                echo "data: }\n";
                echo "\n";
                ob_flush();
                flush();
                # 结算token
                $model = new ChatGptService();
                $model->mjSettlement($sendBoc, $task_id, $function_id);
            });
            $response->headers->set('Content-Type', 'text/event-stream');
            $response->headers->set('X-Accel-Buffering', 'no');
            $response->headers->set('Cache-Control', 'no-cache');

            $response->send();
            return;
        } catch (Exception  $e) {
            if ($e instanceof RequestException && $e->hasResponse()) {
                $response = $e->getResponse();
                $body = $response->getBody();
                throw new ApiException($body);
            } else {
                throw new ApiException($e->getMessage());
            }
        }
    }

}
