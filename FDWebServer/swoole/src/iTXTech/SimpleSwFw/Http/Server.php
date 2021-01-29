<?php

/*
 * iTXTech SimpleSwFw
 *
 * Copyright (C) 2018-2021 iTX Technologies
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace iTXTech\SimpleSwFw\Http;

use iTXTech\SimpleFramework\Console\Logger;
use iTXTech\SimpleFramework\Console\TextFormat;
use iTXTech\SimpleSwFw\Http\Page\AbstractPage;
use Swoole\Coroutine\Http\Server as HttpServer;
use Swoole\Http\Request;
use Swoole\Http\Response;
use function Co\run;

class Server{
	private $serverInfo = "iTXTech SimpleSwFw";
	/** @var AbstractPage[] */
	private $pages;
	/** @var AbstractPage */
	private $defaultPage = AbstractPage::class;

	private $started = false;
	/** @var HttpServer */
	private $server;

	public function registerPage(string $path, string $class){
		if(!$this->started){
			$this->pages[$path] = $class;
		}
	}

	public function setDefaultPage(string $class) : Server{
		if(!$this->started){
			$this->defaultPage = $class;
		}
		return $this;
	}

	public function setServerInfo(string $serverInfo) : Server{
		if(is_a($serverInfo, AbstractPage::class, true)){
			$this->serverInfo = $serverInfo;
		}
		return $this;
	}

	/*
	 * config format
	 * "address" => Server Address
	 * "port" => Server Port
	 * "swoole" => Swoole config
	 */
	public function load(array $config){
		run(function() use ($config){
			$server = new HttpServer($config["address"], $config["port"]);
			$server->set($config["swoole"]);
			Logger::info(TextFormat::GREEN . $this->serverInfo . " is listening on " . $server->host . ":" . $server->port);
			$server->handle("/", function(Request $request, Response $response) use ($server){
				$response->header("Server", $this->serverInfo);
				$uri = $request->server["request_uri"];
				if(isset($this->pages[$uri])){
					$this->pages[$uri]::process($request, $response, $server);
				}else{
					$this->defaultPage::status(404, $response);
				}
				Logger::info("Request " . TextFormat::LIGHT_PURPLE . $this->defaultPage::getQuery($request) .
					TextFormat::WHITE . " from " . TextFormat::AQUA . $this->defaultPage::getClientIp($request));
			});
			$this->server = $server;
		});
	}

	public function start(){
		run(function(){
			$this->started = true;
			$this->server->start();
		});
	}
}
