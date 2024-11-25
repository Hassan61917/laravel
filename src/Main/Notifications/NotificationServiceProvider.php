<?php

namespace Src\Main\Notifications;

use Src\Main\Support\ServiceProvider;

class NotificationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->registerChannelManager();
        $this->registerNotificationManager();
    }
    protected function registerChannelManager(): void
    {
        $this->app->singleton(IChannelFactory::class, ChannelFactory::class);
        $this->app->singleton(ChannelManager::class);
    }
    protected function registerNotificationManager(): void
    {
        $this->app->singleton(NotificationManager::class);
    }
}
