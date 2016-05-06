<?php
namespace Celltrak\RedisBundle\Component\Client;

use Celltrak\RedisBundle\Exception\RedisScriptException;


class CelltrakRedis extends \Redis
{


    /**
     * Runs the Lua script.
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
        $scriptSha = $this->getScriptSha($script);
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

        $this->clearLastError();

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
     * Returns script's SHA.
     * @param string $script
     * @return string
     */
    protected function getScriptSha($script)
    {
        return sha1($script);
    }

    /**
     * Indicates whether a missing script error was just triggered.
     * @return boolean
     */
    protected function triggeredMissingScriptError()
    {
        $error = $this->getLastError();
        return $error && strpos($error, 'NOSCRIPT') !== false;
    }



}
