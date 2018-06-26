<?php
/**
 * Created by IntelliJ IDEA.
 * User: gurgen
 * Date: 2018-06-26
 * Time: 2:12 PM
 */

namespace App\Providers;


class LuaScripts extends \Illuminate\Queue\LuaScripts
{
    /**
     * @inheritdoc
     */
    public static function size()
    {
        return <<<'LUA'
return redis.call('zcard', KEYS[1]) + redis.call('zcard', KEYS[2]) + redis.call('zcard', KEYS[3])
LUA;
    }

    /**
     * @inheritdoc
     */
    public static function pop()
    {
        return <<<'LUA'
-- Pop the first job off of the queue...
local job = redis.call('zrange', KEYS[1], -1, 1)
local reserved = false

if(job ~= false) then
    redis.call('zrem', KEYS[1], job)
    
    -- Increment the attempt count and place job on the reserved queue...
    reserved = cjson.decode(job)
    reserved['attempts'] = reserved['attempts'] + 1
    reserved = cjson.encode(reserved)
    redis.call('zadd', KEYS[2], ARGV[1], reserved)
end

return {job, reserved}
LUA;
    }

    /**
     * @inheritdoc
     */
    public static function migrateExpiredJobs()
    {
        return <<<'LUA'
-- Get all of the jobs with an expired "score"...
local val = redis.call('zrangebyscore', KEYS[1], '-inf', ARGV[1])

-- If we have values in the array, we will remove them from the first queue
-- and add them onto the destination queue in chunks of 100, which moves
-- all of the appropriate jobs onto the destination queue very safely.
if(next(val) ~= nil) then
    redis.call('zremrangebyrank', KEYS[1], 0, #val - 1)

    for i = 1, #val, 1 do
        redis.call('zincrby', KEYS[2], 1, val[i])
    end
end

return val
LUA;
    }
}