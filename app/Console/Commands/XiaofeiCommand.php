<?php namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use App\User;
use App\Models\Xiaofei;

class XiaofeiCommand extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'meter:xiaofei';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = '每天全体用户执行一次消费水，随机一吨，两吨';

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
	public function fire()
	{
		$this->info('xiaofei start...');

		$users = User::all();

		foreach ($users as $key => $user) {
			// 给每个用户的每个水表产生一条消费记录
			foreach ($user->meters as $key2 => $meter) {
				// 消费
				$xiaofei = rand(1,2) % 2 + 1;

				Xiaofei::create([
					'meter_id' => $meter->id,
					'xiaofei_ton' => $xiaofei,
					'mark' => '水表消费',
				]);

				// 用户的水表减去
				$meter->meter_ton -= $xiaofei;
				$meter->save();


			}
		}

		$this->info('xiaofei end...');
	}

}
