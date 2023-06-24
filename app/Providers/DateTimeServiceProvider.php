<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class DateTimeServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind('datetime', function ($app, $params) {
            $format = $params[0] ?? 'Y-m-d H:i:s';
            return function ($dateTime) use ($format) {
                return $dateTime->format($format);
            };
        });
    }
    public function boot()
    {
        Model::setDateFormat('Y-m-d H:i:s');
        Schema::defaultStringLength(191);

        foreach (Schema::getConnection()->getDoctrineSchemaManager()->listTableNames() as $table) {
            $class = '\\App\\Models\\'.ucfirst(camel_case($table));
            if (class_exists($class)) {
                $model = new $class;
                foreach ($model->getDates() as $date) {
                    $format = $model->getDateFormat();
                    $model->{$date} = new Carbon($model->{$date});
                    $model->dateFormat = 'U';
                    $model->setDateFormat($format);
                    $this->app->bind('datetime.'.$table.'.'.$date, function () use ($format) {
                        return function ($dateTime) use ($format) {
                            return $dateTime->format($format);
                        };
                    });
                }
            }
        }
    }
}
