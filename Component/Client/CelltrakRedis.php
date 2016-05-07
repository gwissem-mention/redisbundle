<?php
namespace Celltrak\RedisBundle\Component\Client;

use Celltrak\RedisBundle\Exception\RedisScriptException;

/**
 * Custom wrapper to the PHP Redis client for the purpose of adding convenience
 * methods.
 *
 * See https://pecl.php.net/package/redis for more information on the "standard"
 * PHP Redis client.
 *
 * @author Mike Turoff
 */
class CelltrakRedis extends \Redis
{


    /**
     * Employs Redis to eval passed Lua script.
     *
     * NOTE: runScript is a convenience method for first attempting to evalSha
     * the script. If the script isn't loaded, it will then call eval. Also,
     * this method attempts to ease running a script by separating the keys and
     * other script arguments into two separate method arguments.
     *
     * @param string $script
     * @param array $keys       Enumerated array of Redis keys referenced by the
     *                          script.
     * @param array $args       Enumerated array of additional arguments
     *                          referenced by the script.
     * @return mixed
     * @throws RedisScriptException
     */
    public function runScript($script, array $keys = [], array $args = [])
    {
        $scriptSha = sha1($script);
        $numKeys = count($keys);

        if ($numKeys && ($prefix = $this->getOption(\Redis::OPT_PREFIX))) {
            $keys = array_map(
                function($key) use ($prefix) {
                    return $prefix . $key;
                },
                $keys
            );
        }

        $allArgs = array_merge($keys, $args);

        $result = $this->evalSha($scriptSha, $allArgs, $numKeys);

        if (!$result && $this->triggeredMissingScriptError()) {
            // Script hasn't been loaded, yet. Need to run using eval.
            // This will also load the script for future calls to evalSha.

            // Clear last error so it can be accurately determined whether
            // this second attempt also triggered an error. If it did, then
            // there's an issue with the script.
            $this->clearLastError();

            $result = $this->eval($script, $allArgs, $numKeys);

            if (!$result && ($error = $this->getLastError())) {
                $message = "Error '{$error}' loading Redis script '{$script}'";
                throw new RedisScriptException($message);
            }
        }

        return $result;
    }

    /**
     * Indicates whether a missing script error was just triggered.
     * @return boolean
     */
    public function triggeredMissingScriptError()
    {
        $error = $this->getLastError();
        return $error && strpos($error, 'NOSCRIPT') !== false;
    }

}
