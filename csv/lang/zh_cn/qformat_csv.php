<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * Strings for component 'qformat_csv', language 'en'
 *
 * @package qformat_csv
 * @copyright 2019 hunterhqm <hunterhqm@sina.cn>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$string ['pluginname'] = 'CSV 格式';
$string ['pluginname_help'] = '此插件是导入CSV格式的多选题（multichoice，All-or-nothing 插件必须安装，各选项以英文“;”分隔），判断题（Truefalse），填空题（Shortanswer,支持不同答案不同得分），问答题（Essay）。格式请参考示例文件：<br/>
		<a href="/question/format/csv/sampleMultichoice.csv">sampleMultichoice.csv</a><br />
		<a href="/question/format/csv/sampleTruefalse.csv">sampleTruefalse.csv</a><br />
		<a href="/question/format/csv/sampleShortanswer.csv">sampleShortanswer.csv</a><br />
		<a href="/question/format/csv/sampleEssay.csv">sampleEssay.csv</a><br />';
$string ['pluginname_link'] = 'qformat/csv';
$string ['commma_error'] = '<font color="#990000"> 上传失败，在试题 <b> Question {$a} </b>发现多余的英文“,”。<br /> 请移除该“,”，或者将整段文字以" "括起来。 <br /></font>';
$string ['newline_error'] = '<font color="#990000">上传失败，在试题<b> Question {$a} </b>中发现新行， 请将整个试题和答案写在一行中。<br /> </font>';
$string ['csv_file_error'] = '<font color="#990000">上传失败。在第 <b/> {$a} . </b>行中发现列数与标题行不匹配。<br /> 未导入试题。</font>';
$string ['csv_choices_error'] = '<font color="#990000">上传失败。请确认试题 <b> Question {$a} . </b>的选项以英文“;”分隔，且至少有3个选项。<br />未导入试题。</font>';
$string ['noquestion_error'] = '<font color="#990000">上传失败，请确认第二行有试题数据。<br />未导入试题。</font>';
$string ['noanswer_error'] = '<font color="#990000">上传失败，请确保 <b> Question {$a} </b>至少有一个正确答案。<br /> 未导入试题。</font>';
