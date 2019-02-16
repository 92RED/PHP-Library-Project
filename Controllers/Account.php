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

namespace Controllers;


use Models\EModel;
use Views\Error;
use Views\PageBuilder;
use Views\Template;

class Account extends EModel implements EController
{
    protected $account;

    public function __construct()
    {
        $this->account = new EModel("account", array("id", "username", "password"));
        if (!isset($this->account->id))
        {
            $this->init();
            new Account();
            exit;
        }
    }

    public function init()
    {
        $this->account->adapter->request("CREATE TABLE {$this->account->modelTableName} (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(30) NOT NULL,
        password VARCHAR(32) NOT NULL
        ) ");

        // Creates the admin user, with  the username and password admin. The passwords use md5 to be stored into the
        // database for security.
        $tmp = md5('admin');
        $this->account->insert("'0', 'admin', '{$tmp}'");
    }

    public function def()
    {
        // If the user isn't logged in, then make them login
        if (!isset($_COOKIE["user"]))
        {
            $this->login();
        }
        // Otherwise, show their profile
        else
        {
            $this->profile();
        }
    }

    /**
     * Logs the user in, checking credentials and writing a cookie.
     */
    public function login()
    {
        $page = new PageBuilder("Login Page");

        // Display the login form
        $template = new Template("account/login");
        $template->setTagValue("messages", "");

        $parser = new URLParser();

        if ($parser->checkPOST('username') && $parser->checkPOST('password'))
        {
            $tmpPass = md5($_POST['password']);

            $findAccount = $this->account->findCondition("username = '{$_POST['username']}' AND password = '{$tmpPass}'");

            if (isset($findAccount[0]['username']))
            {
                $template = new Template("account/message");
                $template->setTagValue("message", "You've been logged in!");
                $this->account->id = $findAccount[0]['id'];
                setcookie("user", $this->account->id, time() + (86400 * 30), "/");
            }
            else
            {
                $template->setTagValue("messages", "Invalid username/password combination!");
            }
        }
        else
        {
            $template->setTagValue("messages", "You must insert both username, and password!");
        }

        $page->content($template->generate(true));
        $page->build();
    }

    /**
     * Logs the user out.
     */
    public function logout()
    {
        $page = new PageBuilder("Logout");

        if (isset($_COOKIE['user']))
        {
            unset($_COOKIE['user']);
            setcookie('user', null, -1, '/');
            $page->content("Logged out.");
        }
        else
        {
            $page->content(new Error("You must be logged in to log out."));
        }
        $page->build();
    }
    /**
     * Displays the user profile
     */
    public function profile()
    {
        $page = new PageBuilder("Profile Page");
        $parser = new URLParser();

        if (isset($_COOKIE["user"]))
        {
            if ($parser->get('id'))
            {
                // Check if target ID exists
                $this->account->id = $parser->get('id');
                $result = $this->account->findById();

                if (sizeof($result) > 0)
                {
                    $this->account->id = $_COOKIE["user"];
                    // If it does display things IF USER IS EQUAL OR ADMIN
                    if ($_COOKIE["user"] == $parser->get('id') || $this->account->id == 1)
                    {
                        $this->account->id = $result[0]['id'];
                        $this->account->username = $result[0]['username'];
                        $this->account->password = $result[0]['password'];

                        $template = new Template("account/profile");

                        $template->setTagValue("id", $this->account->id);
                        $template->setTagValue("username", $this->account->username);
                        $template->setTagValue("password", $this->account->password);
                        $template->setTagValue("error", "");
                        $template->setTagValue("hint", "");

                        if ($parser->checkPOST('password'))
                        {
                            $this->account->update("password", md5($_POST['password']));
                            $template->setTagValue("hint", "Password changed!");
                        } else {
                            $template->setTagValue("error", "You must fill in a password to change it!");
                        }

                        $page->content($template->generate(true));
                    }
                    else
                    {
                        $page->content(new Error("Can't edit other profiles without permission."));
                    }
                }
            }
            else
            {
                // If it's a normal user then we just display their profile...
                if ($_COOKIE["user"] != 1)
                {
                    $template = new Template("account/profile");
                    $this->account->id = $_COOKIE["user"];
                    $result = $this->account->findById();

                    $this->account->id = $result[0]['id'];
                    $this->account->username = $result[0]['username'];
                    $this->account->password = $result[0]['password'];

                    $template->setTagValue("id", $this->account->id);
                    $template->setTagValue("username", $this->account->username);
                    $template->setTagValue("password", $this->account->password);
                    $template->setTagValue("error", "");
                    $template->setTagValue("hint", "");

                    $page->content($template->generate(true));

                }
                // If it's the admin then we display a list of users...
                else
                {
                    $page = new PageBuilder("Accounts");

                    $templates = array();

                    foreach ($this->account->findAll() as $account)
                    {
                        $template = new Template("account/userinfo");

                        $template->setTagValue("id", $account["id"]);
                        $template->setTagValue("username", $account["username"]);

                        $templates[] = $template;
                    }

                    $bakedTemplates = Template::mergeTemplates($templates, true);

                    $listpage = new Template("account/list");
                    $listpage->setTagValue("accounts", $bakedTemplates);

                    $this->viewable = $listpage->generate(true);
                    $page->content($this->viewable);
                }
            }
        }
        else
        {
            $page->content(new Error("Must be logged in to see your account."));
        }

        $page->build();
    }

    /**
     * Registers the user.
     */
    public function register()
    {
        $page = new PageBuilder("Registration Page");

        if (isset($_COOKIE['user']))
        {
            $page->content(new Error("You must be logged out to register."));
        }
        else
        {
            $template = new Template("account/register");
            $template->setTagValue("error", "");
            $template->setTagValue("hint", "");

            $parser = new URLParser();

            if (!is_null($parser->checkPOST('username')) && !is_null($parser->checkPOST('password')))
            {
                $result = $this->account->findCondition("username = '{$_POST['username']}'");

                if (sizeof($result) > 0)
                {
                    $template->setTagValue("error", "Username already taken!");
                }
                else
                {
                    $lastId = $this->account->findLast()['id']+1;
                    $pass = md5($_POST['password']);

                    $this->account->insert("'{$lastId}', '{$_POST['username']}', '{$pass}'");

                    $template->setTagValue("hint", "Account created!");
                }
            }
            else
            {
                $template->setTagValue("error", "You must insert both username, and password!");
            }

            $page->content($template->generate(true));
        }

        $page->build();
    }

    /**
     * Deletes a given account.
     */
    public function remove()
    {
        $page = new PageBuilder("Account Deletion");

        $parser = new URLParser();

        if (isset($_COOKIE['user']) && $_COOKIE['user'] == 1)
        {
            if ($parser->get('id')) {
                if ($parser->get('id') != 1) {
                    $this->account->id = $parser->get('id');
                    $this->account->delete();
                    $page->content("Account deleted.");
                } else {
                    $page->content(new Error("Can't delete admin account."));
                }
            } else {
                $page->content(new Error("Id required for deletion."));
            }
        }
        else
        {
            $page->content(new Error("Must be logged in as admin to perform action."));
        }

        $page->build();
    }
}