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

namespace Engine;


class Config
{
    protected $settings_file = "config.ini";

    public function __construct() {
        if (file_exists($this->settings_file)) {
            return $this->load();
        } else {
            if (!isset($this->dbUser)) {
                $this->dbUser = "root";
                $this->dbPass = "root";
                $this->dbName = "notes";
                $this->dbHost = "localhost";
                $this->appName = "Note App";
                $this->appPrefix = "notes_";
                $this->appSuffix = "";
            }
            return $this->create();
        }
    }

    public function purge() {
        return unlink($this->settings_file);
    }

    private function load() {
        $settings = parse_ini_file($this->settings_file);
        $this->dbUser = $settings['dbUser'];
        $this->dbPass = $settings['dbPass'];
        $this->dbName = $settings['dbName'];
        $this->dbHost = $settings['dbHost'];
        $this->appName = $settings['appName'];
        $this->appPrefix = $settings['appPrefix'];
        $this->appSuffix = $settings['appSuffix'];
    }

    public function create() {
        file_put_contents($this->settings_file, "[database]\n", FILE_APPEND);
        file_put_contents($this->settings_file, "dbUser = $this->dbUser\n", FILE_APPEND);
        file_put_contents($this->settings_file, "dbPass = $this->dbPass\n", FILE_APPEND);
        file_put_contents($this->settings_file, "dbName = $this->dbName\n", FILE_APPEND);
        file_put_contents($this->settings_file, "dbHost = $this->dbHost\n", FILE_APPEND);
        file_put_contents($this->settings_file, "\n[application]\n", FILE_APPEND);
        file_put_contents($this->settings_file, "appName = $this->appName\n", FILE_APPEND);
        file_put_contents($this->settings_file, "appPrefix = $this->appPrefix\n", FILE_APPEND);
        file_put_contents($this->settings_file, "appSuffix = $this->appSuffix\n", FILE_APPEND);
        return $this->load();
    }

}