protected function schedule(Schedule $schedule): void
{
// Run the expiry check daily at midnight
$schedule->command('listings:clean')->daily();
}