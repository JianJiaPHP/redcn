<?php

use App\Http\Controllers\Admin\Base\ConfigController;
use App\Http\Controllers\Admin\Base\MeController;
use App\Http\Controllers\Admin\Base\PayController;
use App\Http\Controllers\Admin\Base\UploadController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BankController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\ChatGptController;
use App\Http\Controllers\Api\GoodsController;
use App\Http\Controllers\Api\IncomeController;
use App\Http\Controllers\Api\IndexController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\MidJourneyController;
use App\Http\Controllers\Api\NewsController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\RecommendController;
use App\Http\Controllers\Api\SignController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

# 不需要token身份验证  无需登录
Route::group([], function () {
    # 账号密码登录
    Route::post('login', [AuthController::class, 'login']);
    # 图形验证码
    Route::get('captcha', [AuthController::class, 'captcha']);
    # 注册
    Route::post('register', [AuthController::class, 'register']);
    # 支付宝异步回调
    Route::post('notify', [PayController::class, 'notify']);
});
# 需要身份验证    需要登录
Route::middleware(['api.user'])->group(function () {
    # 首页资料
    Route::get('index', [IndexController::class, 'index']);
    # 赠送金额页面数据
    Route::get('bonusData', [IncomeController::class, 'bonusData']);
    # 福利介绍页面
    Route::get('welfare',[IndexController::class,'welfare']);
    # 轮询查询订单状态
    Route::get('query/results', [PayController::class, 'queryResults']);
    # 验证交易密码是否正确
    Route::post('checkPayPassword', [AuthController::class, 'checkPayPassword']);
    # 修改密码
    Route::post('changePassword', [AuthController::class, 'changePassword']);
    # 修改交易密码
    Route::post('changePayPassword', [AuthController::class, 'changePayPassword']);
    # 上传文件
    Route::post('me/upload', [UploadController::class, 'upload']);
    # 获取用户信息
    Route::get('me', [AuthController::class, 'me']);
    # 用户信息更新
    Route::post('me/update', [AuthController::class, 'update']);
    # 退出登录
    Route::post('logout', [AuthController::class, 'logout']);
    # 获取配置
    Route::get('getConfig', [ConfigController::class, 'getAll']);
    # 获取公告
    Route::get('getOne', [ConfigController::class, 'getOne']);
    # 个人中心
    Route::prefix('user')->group(function () {
        # 我的下级
        Route::get('myBelow', [UserController::class, 'myBelow']);
        # 我的一级下级信息
        Route::get('myBelowOne', [UserController::class, 'myBelowOne']);
        # 我的二级下级信息
        Route::get('myBelowTwo', [UserController::class, 'myBelowTwo']);
        # 我的收支明细
        Route::get('walletDetails', [UserController::class, 'walletDetails']);
        # 分享
        Route::get('share', [UserController::class, 'share']);
        # 查询当前我的连续签到数据
        Route::get('getSignData', [SignController::class, 'getSignData']);
        # 签到
        Route::post('userSign', [SignController::class, 'userSign']);
        # 获取实名信息
        Route::get('getRealName', [UserController::class, 'getRealName']);
        # 实名认证添加
        Route::post('addRealName', [UserController::class, 'addRealName']);
        # 设置留言
        Route::post('setBoard', [UserController::class, 'setBoard']);
        # 获取我的留言
        Route::get('getBoard', [UserController::class, 'getBoard']);
        # 获取中国梦个人信息
        Route::get('getDream', [UserController::class, 'getDream']);
        # 设置中国梦个人信息
        Route::post('setDream', [UserController::class, 'setDream']);
        # 我的余额流水
        Route::get('userAccountList', [IncomeController::class, 'userAccountList']);
        # 奖金钱包流水
        Route::get('userAccountBonusList', [IncomeController::class, 'userAccountBonusList']);
    });
    # 银行卡
    Route::prefix('bank')->group(function () {
        # 我的银行卡列表
        Route::get('myBank', [BankController::class, 'myBank']);
        # 添加银行卡
        Route::post('addBank', [BankController::class, 'addBank']);
        # 删除银行卡
        Route::post('delBank', [BankController::class, 'delBank']);
    });
    # 产品
    Route::prefix('goods')->group(function () {
        # 指定产品列表
        Route::get('list', [GoodsController::class, 'list']);
        # 产品购买记录
        Route::get('payLog', [OrderController::class, 'payLog']);
        # 产品收益记录
        Route::get('incomeLog', [OrderController::class, 'incomeLog']);
    });
    # 订单
    Route::prefix('order')->group(function () {
        # 我的加入社群商品信息
        Route::get('membershipGoods', [OrderController::class, 'membershipGoods']);
        # GPT商品列表
        Route::get('gptGoods', [OrderController::class, 'gptGoods']);
        # 指定商品详细信息
        Route::get('goodsDetail', [OrderController::class, 'goodsDetail']);
        # 发起订单
        Route::post('createOrder', [OrderController::class, 'createOrder']);
        # 订单详情
        Route::get('orderDetail', [OrderController::class, 'orderDetail']);
        # 订单列表
        Route::get('myOrderList', [OrderController::class, 'myOrderList']);
        # 查询商品分类
        Route::get('goodsClass', [OrderController::class, 'goodsClass']);
        # 查询指定分类套餐信息
        Route::get('classGoodsList', [OrderController::class, 'classGoodsList']);
    });
});

