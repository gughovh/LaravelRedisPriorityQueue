<?php
/**
 * Created by IntelliJ IDEA.
 * User: gurgen
 * Date: 2018-06-26
 * Time: 1:58 PM
 */

namespace Aragil\Queue;


use Illuminate\Queue\RedisQueue;
use Illuminate\Support\Arr;

class RedisPriorityQueue extends RedisQueue
{
    public function size($queue = null)
    {
        $queue = $this->getQueue($queue);

        return $this->getConnection()->eval(
            LuaScripts::size(), 3, $queue, $queue.':delayed', $queue.':reserved'
        );
    }

    public function pushRaw($payload, $queue = null, array $options = [])
    {
        $this->getConnection()->zincrby($this->getQueue($queue), 1, $payload);

        return Arr::get(json_decode($payload, true), 'id');
    }

    public function migrateExpiredJobs($from, $to)
    {
        return $this->getConnection()->eval(
            LuaScripts::migrateExpiredJobs(), 2, $from, $to, $this->currentTime()
        );
    }

    protected function retrieveNextJob($queue)
    {
        return $this->getConnection()->eval(
            LuaScripts::pop(), 2, $queue, $queue.':reserved',
            $this->availableAt($this->retryAfter)
        );
    }

    protected function getRandomId()
    {
        return null;
    }
}