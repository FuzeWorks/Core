<?php
/**
 * FuzeWorks Framework Core.
 *
 * The FuzeWorks PHP FrameWork
 *
 * Copyright (C) 2013-2018 TechFuze
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 * @author    TechFuze
 * @copyright Copyright (c) 2013 - 2018, Techfuze. (http://techfuze.net)
 * @license   https://opensource.org/licenses/MIT MIT License
 *
 * @link  http://techfuze.net/fuzeworks
 * @since Version 1.2.0
 *
 * @version Version 1.2.0
 */
$string = '<h3>FuzeWorks debug log</h3>';
$layer = 0;
foreach ($logs as $log) {
    if ($log['type'] == 'LEVEL_START') {
        ++$layer;
        $color = 255 - ($layer * 25);
        $string .= '<div style="background: rgb(188 , 232 ,' . $color . ');border: 1px black solid;margin: 5px 0;padding: 5px 20px;">';
        $string .= '<div style="font-weight: bold; font-size: 11pt;">' . $log['message'] . '<span style="float: right">' . (!empty($log['runtime']) ? '(' . round($log['runtime'] * 1000, 4) . 'ms)' : '') . '</span></div>';
    } elseif ($log['type'] == 'LEVEL_STOP') {
        --$layer;
        $string .= '</div>';
    } elseif ($log['type'] == 'ERROR') {
        $string .= '<div style="' . ($layer == 0 ? 'padding-left: 21px;' : '') . 'font-size: 11pt; background-color:#f56954;">[' . $log['type'] . ']' . (!empty($log['context']) && is_string($log['context']) ? '<u>[' . $log['context'] . ']</u>' : '') . ' ' . $log['message'] . '
        	<span style="float: right">' . (!empty($log['logFile']) ? $log['logFile'] : '') . ' : ' . (!empty($log['logLine']) ? $log['logLine'] : '') . '(' . round($log['runtime'] * 1000, 4) . ' ms)</span></div>';
    } elseif ($log['type'] == 'WARNING') {
        $string .= '<div style="' . ($layer == 0 ? 'padding-left: 21px;' : '') . 'font-size: 11pt; background-color:#f39c12;">[' . $log['type'] . ']' . (!empty($log['context']) && is_string($log['context']) ? '<u>[' . $log['context'] . ']</u>' : '') . ' ' . $log['message'] . '
        	<span style="float: right">' . (!empty($log['logFile']) ? $log['logFile'] : '') . ' : ' . (!empty($log['logLine']) ? $log['logLine'] : '') . '(' . round($log['runtime'] * 1000, 4) . ' ms)</span></div>';
    } elseif ($log['type'] == 'INFO') {
        $string .= '<div style="' . ($layer == 0 ? 'padding-left: 21px;' : '') . 'font-size: 11pt;">' . (!empty($log['context']) ? '<u>[' . $log['context'] . ']</u>' : '') . ' ' . $log['message'] . '<span style="float: right">(' . round($log['runtime'] * 1000, 4) . ' ms)</span></div>';
    } elseif ($log['type'] == 'DEBUG') {
        $string .= '<div style="' . ($layer == 0 ? 'padding-left: 21px;' : '') . 'font-size: 11pt; background-color:#CCCCCC;">[' . $log['type'] . ']' . (!empty($log['context']) ? '<u>[' . $log['context'] . ']</u>' : '') . ' ' . $log['message'] . '<span style="float: right">(' . round($log['runtime'] * 1000, 4) . ' ms)</span></div>';
    }
}

echo $string;