<?php

namespace SurrealCristian;

use Exception;

class OracleClient
{
    protected $conn;

    /**
     * Constructor.
     *
     * @param string $username         Username
     * @param string $password         Password
     * @param string $connectionString Connection string (see http://php.net/manual/en/function.oci-connect.php)
     * @param string $characterSet     Character set
     */
    public function __construct(
        $username, $password, $connectionString = null, $characterSet = null
    ) {
        if ($connectionString === null && $characterSet === null) {
            $this->conn = oci_connect($username, $password);
        } else if ($connectionString !== null && $characterSet === null) {
            $this->conn = oci_connect($username, $password, $connectionString);
        } else {
            $this->conn = oci_connect(
                $username, $password, $connectionString, $characterSet
            );
        }

        if ($this->conn === false) {
            $context = [
                'username' => $username,
                'password' => $password,
                'connectionString' => $connectionString,
                'characterSet' => $characterSet,
            ];

            $error = oci_error();

            $context['error'] = ($error === false)
                ? 'Unknown error'
                : $error;

            throw new Exception('Could not connect. ' . json_encode($context));
        }
    }

    public function __destruct()
    {
        if (is_resource($this->conn)) {
            oci_close($this->conn);
        }
    }

    /**
     * Gets the rows as an array.
     *
     * @param string $sql      SQL query
     * @param array  $bindings Bindings as an associative array
     *
     * @return array
     */
    public function all($sql, array $bindings = null)
    {
        $statementId = oci_parse($this->conn, $sql);

        if ($statementId === null) {
            $context = ['sql' => $sql];

            $error = oci_error();

            $context['error'] = ($error === false)
                ? 'Unknown error'
                : $error;

             throw new Exception('Could not parse. ' . json_encode($context));
        }

        if ($bindings !== null) {
            $this->bindParameters($statementId, $bindings);
        }

        if (oci_execute($statementId) === false) {
            $context = [
                'sql' => $sql,
                'bindings' => $bindings,
            ];

            $error = oci_error();

            $context['error'] = ($error === false)
                ? 'Unknown error'
                : $error;

            oci_free_statement($statementId);

            throw new Exception('Could not execute. ' . json_encode($context));
        }

        $rows = [];
        $skip = 0;
        $maxrows = -1;
        $flags = OCI_FETCHSTATEMENT_BY_ROW + OCI_ASSOC;

        $nrows = oci_fetch_all($statementId, $rows, $skip, $maxrows, $flags);

        if ($nrows === false) {
            $context = [];

            $error = oci_error();

            $context['error'] = ($error === false)
                ? 'Unknown error'
                : $error;

            oci_free_statement($statementId);

            throw new Exception('Could not fetch all the rows. ' . json_encode($context));
        }

        return $rows;
    }

    /**
     * Yields the rows.
     *
     * @param string $sql      SQL query
     * @param array  $bindings Bindings as an associative array
     *
     * @return Generator
     */
    public function yieldAll($sql, array $bindings = null)
    {
        $statementId = oci_parse($this->conn, $sql);

        if ($statementId === null) {
            $context = ['sql' => $sql];

            $error = oci_error();

            $context['error'] = ($error === false)
                ? 'Unknown error'
                : $error;

             throw new Exception('Could not parse. ' . json_encode($context));
        }

        if ($bindings !== null) {
            $this->bindParameters($statementId, $bindings);
        }

        if (oci_execute($statementId) === false) {
            $context = [
                'sql' => $sql,
                'bindings' => $bindings,
            ];

            $error = oci_error();

            $context['error'] = ($error === false)
                ? 'Unknown error'
                : $error;

            oci_free_statement($statementId);

            throw new Exception('Could not execute. ' . json_encode($context));
        }

        while (($row = oci_fetch_assoc($statementId)) !== false) {
            yield $row;
        }

        oci_free_statement($statementId);
    }

    /**
     * Executes an INSERT, UPDATE or DELETE.
     *
     * @param string $sql      SQL query
     * @param array  $bindings Bindings as an associative array
     *
     * @return integer Affected rows
     */
    public function execute($sql, array $bindings = null)
    {
        $statementId = oci_parse($this->conn, $sql);

        if ($statementId === null) {
            $context = ['sql' => $sql];

            $error = oci_error();

            $context['error'] = ($error === false)
                ? 'Unknown error'
                : $error;

             throw new Exception('Could not parse. ' . json_encode($context));
        }

        if ($bindings !== null) {
            $this->bindParameters($statementId, $bindings);
        }

        if (oci_execute($statementId) === false) {
            $context = [
                'sql' => $sql,
                'bindings' => $bindings,
            ];

            $error = oci_error();

            $context['error'] = ($error === false)
                ? 'Unknown error'
                : $error;

            oci_free_statement($statementId);

            throw new Exception('Could not execute. ' . json_encode($context));
        }

        $nRowsAffected = oci_num_rows($statementId);

        oci_free_statement($statementId);

        return $nRowsAffected;
    }

    /**
     * Commits the outstanding database transaction for the connection.
     */
    public function commit()
    {
        if (oci_commit($this->conn) === false) {
            $context = [];

            $error = oci_error();

            $context['error'] = ($error === false)
                ? 'Unknown error'
                : $error;

            throw new Exception('Could not commit. ' . json_encode($context));
        }
    }

    protected function bindParameters($statementId, array $bindings)
    {
        foreach ($bindings as $key => $value) {
            $res = oci_bind_by_name($statementId, $key, $bindings[$key]);

            if ($res === false) {
                $context = [
                    'key' => $key,
                    'value' => $value,
                ];

                $error = oci_error();

                $context['error'] = ($error === false)
                    ? 'Unknown error'
                    : $error;

                oci_free_statement($statementId);

                throw new Exception(
                    'Could not bind parameter by name. ' . json_encode($context)
                );
            }
        }
    }
}
