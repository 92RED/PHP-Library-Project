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

namespace Models;


use Database\DatabaseAdapter;
use Engine\Config;

abstract class AbstractMapper extends AbstractList
{
    protected $adapter;
    protected $config;
    protected $prefix;
    protected $suffix;
    protected $modelTableName;

    /**
     * Sets the database adapter for the current model.
     * @param DatabaseAdapter $adapter
     */
    protected function setAdapter(DatabaseAdapter $adapter)
    {
        $this->adapter = $adapter;

        $this->config = new Config();
        $this->prefix = $this->config->appPrefix;
        $this->suffix = $this->config->appSuffix;
        $this->modelTableName = $this->prefix . $this->name . $this->suffix;
    }

    /**
     * Checks if table related to the model exists.
     */
    protected function checkTable()
    {
        if (is_bool($this->adapter->select("1")->from($this->modelTableName)->limit(1)->send()))
        {
            return false;
        }
        else
        {
            return true;
        }
    }
    /**
     * Returns a row by the given id from a model.
     * @param string $modelName The name of the model.
     * @param array $modelMembers An array containing the members.
     * @return array Should return an array that contains the row data.
     */
    protected function findById()
    {
        $id = $this->members[0];

        return $this->adapter->select($this->membersToString())->
        from($this->modelTableName)->where("{$id} = {$this->$id}")->
        send();
    }

    /**
     * Finds, and returns all the rows found in the database.
     * @return PDO Returns the result.
     */
    protected function findAll()
    {
        return $this->adapter->select($this->membersToString())->
        from($this->modelTableName)->send();
    }

    /**
     * Finds and returns anything found in the dabase with given
     * conditions from the user.
     * @param $condition string the condition
     * @return PDO Returns the result
     */
    protected function findCondition($condition)
    {
        return $this->adapter->select($this->membersToString())->
        from($this->modelTableName)->where($condition)->send();
    }

    /**
     * Finds the first entry in the current model.
     * @return PDO The resulting first row.
     */
    protected function findFirst()
    {
        if (!is_bool($this->findAll()))
        {
            return $this->adapter->select($this->membersToString())->
            from($this->modelTableName)->limit(1)->send()[0];
        }
    }

    protected function findLast()
    {
        if (!is_bool($this->findAll()))
        {
            return $this->adapter->select($this->membersToString())->
            from($this->modelTableName)->sort($this->members[0], "DESC")->limit(1)->send()[0];
        }
    }

    protected function insert($values)
    {
        return $this->adapter->insert($this->modelTableName, $this->membersToString())->values($values)->send();
    }

    protected function update($member, $value)
    {
        $id = $this->members[0];
        return $this->adapter->update($this->modelTableName)->set($member, $value)->
        where("{$id} = {$this->$id}")->send();
    }

    protected function delete()
    {
        $id = $this->members[0];
        return $this->adapter->delete($this->modelTableName)->where("{$id} = {$this->$id}")->send();
    }

    /**
     * Returns all the members of the current model into a string.
     * @return string All the members in a string/
     */
    private function membersToString()
    {
        return implode(",", $this->members);
    }
}