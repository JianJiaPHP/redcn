<?php

namespace App\Providers;

use AlibabaCloud\Tea\Model;
use Carbon\Carbon;
use FFMpeg\FFMpeg;
use FFMpeg\FFProbe;
use Illuminate\Support\ServiceProvider;
use Reliese\Coders\CodersServiceProvider;
use Illuminate\Support\Traits\Localizable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Date;
class AppServiceProvider extends ServiceProvider
{
    use Localizable;
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app->environment() == 'local') {
            $this->app->register(CodersServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //       $ffmpeg = FFMpeg::create(array(
//           'ffmpeg.binaries'  => '/usr/local/ffmpeg/ffmpeg',
//           'ffprobe.binaries' => '/usr/local/ffmpeg/ffprobe',
//           'timeout'          => 3600, // The timeout for the underlying process
//           'ffmpeg.threads'   => 12,   // The number of threads that FFMpeg should use
//       ));
        $this->app->singleton('ffmpeg', function ($app) {
            return FFMpeg::create([
                'ffmpeg.binaries'  => '/www/php_session/ffmpeg-4.3.1/ffmpeg',
                'ffprobe.binaries' =>  '/www/php_session/ffmpeg-4.3.1/ffprobe'
            ]);
        });
        $this->app->singleton('ffprobe', function ($app) {
            return FFProbe::create([
                'ffprobe.binaries' =>  '/www/php_session/ffmpeg-4.3.1/ffprobe'
            ]);
        });
    }
}
