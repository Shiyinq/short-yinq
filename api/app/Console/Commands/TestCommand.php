<?php
/**
 *
 * PHP version >= 7.0
 *
 * @category Console_Command
 * @package  App\Console\Commands
 */

namespace App\Console\Commands;


use App\Post;

use Exception;

use Illuminate\Console\Command;



/**
 * Class deletePostsCommand
 *
 * @category Console_Command
 * @package  App\Console\Commands
 */
class TestCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = "test:mytest {name=null}";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Delete all posts";


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        echo "hello ".$this->argument('name');
    }
}
