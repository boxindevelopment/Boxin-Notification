<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class NotifReminderPaymentSchedule extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'Notif:ReminderPayment';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notification : Reminder Payment';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //
    }
}
