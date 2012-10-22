<?php

namespace MistyDoctrine\Transaction;

use MistyDoctrine\Dao;

abstract class Transactional
{
    protected $dao;

    public function __construct(Dao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * Takes a Closure $func, wraps it in a database transaction, and execute
     * it injecting $this as first argument (you can access private methods/vars)
     *
     * In case of failures it calls handleErrorCallback() and executes $errorCallback
     * This is not enforced to be a Closure, if you want you can pass an error message
     * and override handleErrorCallback() and do something with it
     * (if you pass a string/array without overriding handleErrorCallback() it will error out)
     */
    protected function t(\Closure $func, $errorCallback = null)
    {
        if ($this->dao->hasActiveTransaction()) {
            return $this->useActiveTransaction($func, $errorCallback);
        } else {
            return $this->createAndCommitTransaction($func, $errorCallback);
        }
    }

    /**
     * Override this is you have particular logic around the $erroCallback
     * e.g. pass an error message and use this method to propagate it
     * through your system
     */
    protected function handleError($exception, $errorCallback)
    {
        if ($errorCallback) {
            $errorCallback($exception);
        }
    }

    private function createAndCommitTransaction(\Closure $func, $errorCallback)
    {
        return $this->executeInTransaction(true, $func, $errorCallback);
    }

    private function useActiveTransaction(\Closure $func, $errorCallback)
    {
        return $this->executeInTransaction(false, $func, $errorCallback);
    }

    private function executeInTransaction($newTransaction, \Closure $func, $errorCallback)
    {
        if ($newTransaction) $this->dao->start();
        try {
            $result = $func();

            if ($newTransaction) $this->dao->commit();

            return $result;
        } catch (\Exception $ex) {
            if ($newTransaction) $this->dao->rollback();

            $this->handleError($ex, $errorCallback);

            if ($newTransaction) {
                return null;
            } else {
                echo 'throw? '; // TODO fix me
                throw $ex;
            }
        }
    }
}
