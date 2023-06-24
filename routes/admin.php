<?php

use App\Http\Controllers\Admin\Config\ConfigController;
use App\Http\Controllers\Admin\Feedback\FeedbackController;
use App\Http\Controllers\Admin\Banner\BannerController;
use App\Http\Controllers\Admin\Base\AdministratorController;
use App\Http\Controllers\Admin\Base\AdminMenuController;
use App\Http\Controllers\Admin\Base\AdminResourceController;
use App\Http\Controllers\Admin\Base\AuthController;
use App\Http\Controllers\Admin\Base\LogController;
use App\Http\Controllers\Admin\Base\MeController;
use App\Http\Controllers\Admin\Base\MessageRecordController;
use App\Http\Controllers\Admin\Base\PayController;
use App\Http\Controllers\Admin\Base\RoleController;
use App\Http\Controllers\Admin\Base\UploadController;
use App\Http\Controllers\Admin\Functions\FunctionController;
use App\Http\Controllers\Admin\Goods\GoodsClassController;
use App\Http\Controllers\Admin\Goods\GoodsController;
use App\Http\Controllers\Admin\NavRecommend\NavRecommendController;
use App\Http\Controllers\Admin\News\NewsClassController;
use App\Http\Controllers\Admin\News\NewsController;
use App\Http\Controllers\Admin\News\RecommendController;
use App\Http\Controllers\Admin\Order\OrderController;
use App\Http\Controllers\Admin\Prompt\PromptClassController;
use App\Http\Controllers\Admin\Prompt\PromptController;
use App\Http\Controllers\Admin\Prompt\PromptMidJourneyController;
use App\Http\Controllers\Admin\Subscribe\SubscribeController;
use App\Http\Controllers\Admin\Users\UsersAccountController;
use App\Http\Controllers\Admin\Users\UsersController;
use App\Http\Controllers\Admin\Withdrawal\WithdrawalController;
use App\Http\Controllers\Ping;
use Illuminate\Support\Facades\Route;


# 不需要token身份验证  无需登录
Route::group([], function () {
    # 登录
    Route::post('login', [AuthController::class, 'login']);
    # 获取配置
    Route::get('getConfig/{key}', [\App\Http\Controllers\Admin\Base\ConfigController::class, 'getOne']);
    # 上传文件
    Route::post('me/upload', [UploadController::class, 'upload']);
});

# 需要token身份验证  需要登录
Route::middleware(['admin'])->group(function () {
    /** 订单管理 */
    Route::prefix('order')->group(function (){
        Route::get('/', [OrderController::class, 'index']);
        Route::get('update', [OrderController::class, 'update']);
        Route::get('delete', [OrderController::class, 'destroy']);
    });

    /** 用户管理 */
    Route::prefix('users')->group(function () {
        # 用户管理
        Route::get('/', [UsersController::class, 'index']);
        # 用户资金
        Route::get('account/{id}', [UsersController::class, 'account']);
        # 用户模型
        Route::get('prompt/{id}', [UsersController::class, 'prompt']);
        # 用户的订阅
        Route::get('subscribe/{id}', [UsersController::class, 'subscribe']);
        #给用户赠送订阅
        Route::get('giveAwaySubscribe/{id}', [UsersController::class, 'giveAwaySubscribe']);
        # 用户修改
        Route::put('/{id}', [UsersController::class, 'update']);
        # 获取用户的下级用户
        Route::get('/subsetUserList/{id}', [UsersController::class, 'subsetUserList']);
        # 用户登录日志
        Route::get('login_log', [UsersController::class, 'loginLog']);
        # 用户行为日志
        Route::get('users_log', [UsersController::class, 'usersLog']);
        # 导出
        Route::get('export', [UsersController::class, 'export']);
    });

    # 新闻管理
    Route::apiResource('news', NewsController::class)->except(['show']);
    # 新闻分类管理
    Route::apiResource('/news/class', NewsClassController::class)->except(['show']);
    # 新馆管理--推荐位
    Route::apiResource('/news/recommend', RecommendController::class)->except(['show']);


    # 模型管理
    Route::apiResource('prompt', PromptController::class)->except(['show']);
    # 模型分类管理
    Route::apiResource('/prompt/class', PromptClassController::class)->except(['show']);
    #mid journey 模型
    Route::apiResource('/promptMidJourney', PromptMidJourneyController::class)->except(['show']);
    Route::get('prompt/mid-journey/listAll', [PromptMidJourneyController::class, 'listAll']);
    # banner管理
    Route::apiResource('banner', BannerController::class)->except(['show']);

    # 配置管理
    Route::apiResource('config', ConfigController::class)->except(['show']);

    Route::apiResource('navRecommend', NavRecommendController::class)->except(['show']);
    Route::get('nav/recommendListAll', [NavRecommendController::class, 'listAll']);

    #用户反馈列表
    Route::get('feedback', [FeedbackController::class, 'index']);


    /** 用户资金管理 */
    Route::prefix('users_account')->group(function () {
        # 用户管理
        Route::get('list', [UsersAccountController::class, 'index']);
    });

    /** 订阅管理 */
    Route::prefix('subscribe')->group(function () {
        Route::get('list', [SubscribeController::class, 'index']);
    });
    /** 商品管理 */
    Route::prefix('goods')->group(function () {
        Route::get('list', [GoodsController::class, 'index']);
        Route::post('add', [GoodsController::class, 'add']);
        Route::delete('delete/{id}', [GoodsController::class, 'destroy']);
        Route::put('update/{id}', [GoodsController::class, 'update']);
    });
    /** 商品分类管理 */
    Route::prefix('goods/class')->group(function () {
        Route::get('list', [GoodsClassController::class, 'index']);
        Route::put('update/{id}', [GoodsClassController::class, 'update']);
    });


    /** 方法管理 */
    Route::prefix('function')->group(function () {
        Route::get('/', [FunctionController::class, 'index']);
        Route::put('update/{id}', [FunctionController::class, 'update']);
    });


    # 提现管理模块
    Route::prefix('withdrawal')->group(function () {
        # 获取提现列表
        Route::get('getWithdrawalList', [WithdrawalController::class, 'index']);
        # 修改提现订单状态
        Route::put('approvalWithdrawal/{id}', [WithdrawalController::class, 'changeWithdrawalStatus']);
        # 获取出账方式列表
        Route::get('getPayListAll', [WithdrawalController::class, 'getPayList']);
        # 获取提现配置
        Route::get('getWithdrawalConfig', [WithdrawalController::class, 'getWithdrawalConfig']);
        # 修改提现配置
        Route::post('updateWithdrawalConfig', [WithdrawalController::class, 'updateWithdrawalConfig']);
    });



    Route::prefix('me')->group(function () {
        # 我的个人信息
        Route::get('/', [MeController::class, 'me']);
        # 退出登录
        Route::post('logout', [AuthController::class, 'logout']);
        # 修改个人密码
        Route::put('updatePassword', [MeController::class, 'updatePwd']);
        # 更新个人信息
        Route::post('updateInfo', [MeController::class, 'updateInfo']);
        # 获取登陆者该有的导航栏
        Route::get('getNav', [MeController::class, 'getNav']);
        # 首页统计
        Route::get('homeData', [MeController::class, 'homeData']);
        # 获取我当前角色的所有资源
        Route::get('permission/me/adminAll', [AdminResourceController::class, 'adminAll']);
        # 获取新的google验证密钥
        Route::get('googleNewSecret', [AuthController::class, 'getNewGoogleSecret']);
        # 个人中心-确认换绑google验证器
        Route::post('updateGoogleNewSecret', [AuthController::class, 'updateGoogleNewSecret']);
    });

    Route::middleware(['admin'])->group(function () {
        # 权限管理
//        Route::prefix('permission')->group(function () {
            # 配置管理
            Route::prefix('config')->group(function () {
                # 获取配置列表
                Route::get('/', [ConfigController::class, 'index']);
                # 修改配置
                Route::put('/{id}', [ConfigController::class, 'update']);
                # 根据key值获取value值
                Route::get('/getOne/{key}', [ConfigController::class, 'getOne']);
            });
            # 资源列表
            Route::apiResource('adminResource', AdminResourceController::class)->except(['show']);
            # 所有资源列表
            Route::get('adminResourceAll', [AdminResourceController::class, 'all']);
            # 获取我当前角色的所有资源
            Route::get('me/adminAll', [AdminResourceController::class, 'adminAll']);
            # 菜单管理
            Route::apiResource('adminMenu', AdminMenuController::class)->except(['show']);
            # 所有菜单
            Route::get('adminMenuListAll', [AdminMenuController::class, 'listAll']);
            # 操作日志
            Route::get('operating_log', [LogController::class, 'operatingLog']);#操作日志列表
            Route::get('login_log', [LogController::class, 'loginLog']);#登陆日志列表
            # 角色管理
            Route::apiResource('role', RoleController::class)->except(['show']);
            # 所有角色
            Route::get('rolesAll', [RoleController::class, 'getAll']);
            # 管理员管理
            Route::apiResource('administrators', AdministratorController::class)->except(['show']);
//        });
        # PAY---发起抖音支付退款
        Route::post('douyinRefund', [PayController::class, 'douYinSendRefund']);
        #banner 管理
//        Route::apiResource('banner', BannerController::class)->except(['show']);





        # 系统消息通知
        Route::prefix('message')->group(function () {
            # 发布记录表
            Route::apiResource('record', MessageRecordController::class)->except(['show']);
        });


        # 支付模块
        Route::prefix('pay')->group(function () {
            # 调用支付二维码
            Route::get('scanPay', [PayController::class, 'pay']);
            # 轮询查询订单状态
            Route::get('query/results', [PayController::class, 'queryResults']);
        });
    });


});



