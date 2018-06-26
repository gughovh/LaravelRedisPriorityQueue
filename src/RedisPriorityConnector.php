<?php
/**
 * Created by IntelliJ IDEA.
 * User: gurgen
 * Date: 2018-06-26
 * Time: 1:53 PM
 */

namespace Aragil;


use Illuminate\Queue\Connectors\RedisConnector;
use Illuminate\Support\Arr;
use Aragil\Queue\RedisPriorityQueue;

class RedisPriorityConnector extends RedisConnector
{
    public function connect(array $config)
    {
        return new RedisPriorityQueue(
            $this->redis, $config['queue'],
            Arr::get($config, 'connection', $this->connection),
            Arr::get($config, 'retry_after', 60)
        );
    }
}