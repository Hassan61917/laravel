<?php

namespace Src\Main\Foundation\Console\Commands\Notification;

use Src\Main\Foundation\Console\Commands\AbstractMigrationGenerator;

class NotificationTable extends AbstractMigrationGenerator
{
    protected string $stubPath = "Notification";
    protected string $description = 'Create a migration for the notifications table';
    protected function migrationTableName(): string
    {
        return 'notifications';
    }
    protected function migrationStubFile(): string
    {
        return 'notifications.table.stub';
    }
}
