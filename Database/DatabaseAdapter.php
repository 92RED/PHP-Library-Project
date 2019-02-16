<?php
/**
 * Copyright (C) 2017-2019 RED92.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a
 * copy of this software and associated documentation files (the "Software"),
 * to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense,
 * and/or sell copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.  IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
 * DEALINGS IN THE SOFTWARE.
 *
 * Except as contained in this notice, the name of  shall not be used in
 * advertising or otherwise to promote the sale, use or other dealings in this
 * Software without prior written authorization from .
 */

namespace Database;


namespace Database;


use Engine\Config;
use Views\Error;
use Views\PageBuilder;

class DatabaseAdapter
{
    public $database;
    protected $query;

    public function __construct()
    {
        $config = new Config();

        // We check if the database exists, if it doesn't, we create it
        $connection = "mysql:host={$config->dbHost}";

        $this->database = new \PDO($connection,
            $config->dbUser,
            $config->dbPass);

        $stmt = $this->database->query("SELECT COUNT(*) FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '{$config->dbName}'");
        if ($stmt->fetchColumn() == 0)
        {
            $this->database->exec("CREATE DATABASE `{$config->dbName}`;");
            $this->database = null;
        }
        else
        {

            // If it does then we simply connect
            $connection = "mysql:host={$config->dbHost};dbname={$config->dbName}";

            try {
                $this->database = new \PDO($connection,
                    $config->dbUser,
                    $config->dbPass);
            } catch (\PDOException $e) {
                $page = new PageBuilder("Database Connection");
                $page->content(new Error($e));
                $page->build();
            }
        }

        return $this->database;
    }

    public function request($command)
    {
        try
        {
            //var_dump($command);
            if (!is_null($this->database))
            {
                $result = $this->database->query($command);
                if (!is_bool($result)) {
                    return $result->fetchAll(\PDO::FETCH_ASSOC);
                } else {
                    return $result;
                }
            }
        } catch (\PDOException $e)
        {
            $page = new PageBuilder("Database Query");
            $page->content(new Error($e));
            $page->build();
        }

        $this->database = null;
    }

    public function select($members)
    {
        if (is_array($members))
        {
            $members = implode(",", $this->members);
        }

        if (is_string($members))
        {
            $this->query = "SELECT {$members} ";
        }

        return $this;
    }

    public function from($model)
    {
        if (is_string($model))
        {
            $this->query .= "FROM {$model} ";
        }

        return $this;
    }

    public function join($joins)
    {
        if (is_string($joins))
        {
            $this->query .= "JOIN {$joins}";
        }

        return $this;
    }

    public function where($condition)
    {
        if (is_string($condition))
        {
            $this->query .= "WHERE {$condition} ";
        }

        return $this;
    }

    public function sort($field, $way)
    {
        if (is_string($field) && is_string($way))
        {
            $this->query .= "ORDER BY {$field} {$way} ";
        }

        return $this;
    }

    public function limit($limit)
    {
        if (is_int($limit))
        {
            $this->query .= "LIMIT {$limit} ";
        }

        return $this;
    }

    public function insert($model, $members)
    {
        if (is_string($model))
        {
            $this->query = "INSERT INTO {$model} ({$members}) ";
        }

        return $this;
    }

    public function values($values)
    {
        $this->query .= "VALUES ({$values})";

        return $this;
    }

    public function update($model)
    {
        if (is_string($model))
        {
            $this->query = "UPDATE {$model} ";
        }

        return $this;
    }

    public function set($member, $value)
    {
        $this->query .= "SET {$member} = '{$value}' ";

        return $this;
    }

    public function delete($model)
    {
        if (is_string($model))
        {
            $this->query = "DELETE FROM {$model} ";
        }

        return $this;
    }

    public function send()
    {
        return $this->request($this->query);
    }
}