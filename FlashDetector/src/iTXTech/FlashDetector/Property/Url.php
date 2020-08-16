<?php

/*
 * iTXTech FlashDetector
 *
 * Copyright (C) 2018-2020 iTX Technologies
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

namespace iTXTech\FlashDetector\Property;

use iTXTech\FlashDetector\Arrayable;

class Url extends Arrayable{
	public const IMAGE_LOGO = "logo";

	public $url;
	public $desc;
	protected $img;
	protected $hint;

	public function __construct(string $desc, string $url, string $img = "", string $hint = ""){
		$this->url = $url;
		$this->desc = $desc;
		$this->img = $img;
		$this->hint = $hint;
	}
}
