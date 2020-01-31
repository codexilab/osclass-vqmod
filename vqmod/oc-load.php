<?php
/*
 * @author Adrian Olmedo <adrianolmedo.ve@gmail.com>
 * @copyright (c) 2020 CodexiLab
 *
 * This file is part of vQmod for Osclass.
 *
 * vQmod for Osclass is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * vQmod for Osclass is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with vQmod for Osclass.  If not, see <https://www.gnu.org/licenses/>.
 */
 
// Helpers
require_once VQMOD_PATH . "helpers/hUtils.php";

// Controllers
require_once VQMOD_PATH . "controller/admin/mods.php";
require_once VQMOD_PATH . "controller/admin/logs.php";

// Ajax
require_once VQMOD_PATH . "ajax/ajax.php";

// Classes
require_once VQMOD_PATH . "classes/UGRSR.php";
require_once VQMOD_PATH . "classes/VQModManager.php";