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
 * CSV format question importer.
 *
 * @package qformat_csv
 * @copyright 2018 Gopal Sharma <gopalsharma66@gmail.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined ( 'MOODLE_INTERNAL' ) || die ();
/*
 * CSV format - a simple format for creating multiple and single choice questions.
 * The format looks like this for simple csv file with minimum columns:
 * questionname, questiontext, A, B, C, D, Answer 1, Answer 2
 * Question1, "3, 4, 7, 8, 11, 12, ... What number should come next?",7,10,14,15,D
 *
 *
 * The format looks like this for Extended csv file with extra columns columns:
 * questionname, questiontext, A, B, C, D, Answer 1, Answer 2,
 * answernumbering, correctfeedback, partiallycorrectfeedback, incorrectfeedback, defaultmark
 * Question1, "3, 4, 7, 8, 11, 12, ... What number should come next?",7,10,14,15,D, ,
 * 123, Your answer is correct., Your answer is partially correct., Your answer is incorrect., 1
 *
 *
 * That is,
 * + first line contains the headers separated with commas
 * + Next line contains the details of question, each line contain
 * one question text, four option, and either one or two answers again all separated by commas.
 * Each line contains all the details regarding the one question ie. question text, options and answer.
 * You can also download the sample file for your reference.
 *
 * @copyright 2018 Gopal Sharma <gopalsharma66@gmail.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$globals ['header'] = true;
class qformat_csv extends qformat_default {
	public function provide_import() {
		return true;
	}
	public function provide_export() {
		return true;
	}
	
	/**
	 *
	 * @return string the file extension (including .) that is normally used for
	 *         files handled by this plugin.
	 */
	public function export_file_extension() {
		return '.csv';
	}
	public function readquestions($lines) {
		global $CFG;
		require_once ($CFG->libdir . '/csvlib.class.php');
		
		// 行数少于3行
		if (count ( $lines ) < 2) {
			echo get_string ( 'noquestion_error', 'qformat_csv' );
			return 0;
		}
		$questions = array ();
		$header = trim ( $lines [0] );
		
		$supportCSV = array (
				'multichoice' => 'multichoicename,questiontext,choices,answer,generalfeedback,defaultmark,qtype',
				'essay' => 'essayname,questiontext,responsetemplate,graderinfo,generalfeedback,defaultmark',
				'shortanswer' => 'shortanswername,questiontext,answer,fraction,generalfeedback,defaultmark',
				'truefalse' => 'truefalsename,questiontext,answer,generalfeedback,defaultmark' 
		);
		
		switch ($header) {
			case $supportCSV ['multichoice'] :
				$questions = $this->get_multichoice_questions ( $lines );
				// var_dump($questions);
				break;
			case $supportCSV ['essay'] :
				$questions = $this->get_essay_questions ( $lines );
				break;
			case $supportCSV ['shortanswer'] :
				$questions = $this->get_shortanswer_questions ( $lines );
				break;
			case $supportCSV ['truefalse'] :
				$questions = $this->get_truefalse_questions ( $lines );
				break;
			
			default :
				var_dump ( 'err: ', $header );
				echo get_string ( 'csv_file_error', 'qformat_csv' );
				return 0;
				break;
		}
		
		return $questions;
	}
	
	/**
	 *
	 * @param
	 *        	$rowdata
	 */
	private function get_multichoice_questions($lines) {
		$questions = array ();
		$headers = explode ( ',', $lines [0] );
		
		for($rownum = 1; $rownum < count ( $lines ); $rownum ++) {
			$rowdata = str_getcsv ( $lines [$rownum], ',', '"' ); // Ignore the commas(,) within the double quotes (").
			
			if (count ( $rowdata ) != count ( $headers )) {
				echo get_string ( 'csv_file_error', 'qformat_csv', $rownum );
				return 0;
			}
			// 0=>multichoicename,questiontext,choices,answer,generalfeedback,defaultmark,qtype
			question_bank::get_qtype ( 'multichoice' );
			$question = $this->defaultquestion ();
			$question->name = trim ( $rowdata [0] );
			$question->questiontext = htmlspecialchars ( trim ( $rowdata [1] ), ENT_NOQUOTES );
			$choices = explode ( ';', $rowdata [2] );
			if (count ( $choices ) < 3) {
				return 0;
			}
			$answer = trim ( $rowdata [3] );
			$question->answernumbering = 'ABCD';
			$question->generalfeedback = $this->strToHTMLformat ( trim ( $rowdata [4] ) );
			$question->defaultmark = ( int ) ($rowdata [5]);
			// 处理选项及答案
			// answer, 默认选项序号为ABCD……，
			// 选择题选项至少为3项
			foreach ( $choices as $value ) {
				$question->answer [] = $this->strToHTMLformat ( trim ( $value ) );
				$question->feedback [] = $this->strToHTMLformat ( trim ( '' ) ); // 必填，否则写数据库出错
			}
			$numcorrectans = 0;
			$fraction = array ();
			
			for($ABCD = 0; $ABCD < count ( $choices ); $ABCD ++) {
				if (stripos ( $answer, chr ( 65 + $ABCD ) ) === false) { // 从A开始判断,答案里是否有A。
					$fraction [$ABCD] = 0; // 不在正确答案中，则得分为0
				} else {
					$fraction [$ABCD] = 1;
					$numcorrectans ++;
				}
			}
			
			if ($numcorrectans < 1) {
				echo get_string ( 'noanswer_error', 'qformat_csv', $rownum );
				return 0;
			}
			
			$qtype = trim ( $rowdata [6] );
			switch ($qtype) {
				case 'multichoiceset' :
				case 'multichoice' :
					$question->qtype = 'multichoiceset';
					// all or nothing 插件，正确答案的correctanswer为1，错误答案为0
					$question->correctanswer = $fraction;
					break;
				/*
				 * ///这个提示写入数据库失败，不知道为什么？？？
				 * case 'multichoice' :
				 * $question->qtype = 'multichoice';
				 * $fac = 1 / $numcorrectans;
				 * for($ABCD = 0; $ABCD < count ( $choices ); $ABCD ++) {
				 * if ($fraction [$ABCD] > 0) {
				 * $fraction [$ABCD] = $fac;
				 * }
				 * }
				 * $question->fraction = $fraction;
				 * break;
				 */
				case 'single' :
					$question->qtype = 'multichoice';
					$question->single = 1;
					if (1 != $numcorrectans) {
						echo get_string ( 'single_answer_num_error', 'qformat_csv', $rownum );
						return 0;
					}
					$question->fraction = $fraction;
					break;
				default :
					echo get_string ( 'csv_file_error', 'qformat_csv', $rownum );
					return 0;
					break;
					;
			}
			$questions [] = $question;
		}
		return $questions;
	}
	private function get_truefalse_questions($lines) {
		$questions = array ();
		$headers = explode ( ',', $lines [0] );
		for($rownum = 1; $rownum < count ( $lines ); $rownum ++) {
			$rowdata = str_getcsv ( $lines [$rownum], ',', '"' ); // Ignore the commas(,) within the double quotes (").
			
			if (count ( $rowdata ) != count ( $headers )) {
				echo get_string ( 'csv_file_error', 'qformat_csv', $rownum );
				return 0;
			}
			// 0=>truefalsename,questiontext,answer,generalfeedback,defaultmark
			question_bank::get_qtype ( 'truefalse' );
			$question = $this->defaultquestion ();
			$question->name = trim ( $rowdata [0] );
			$question->qtype = 'truefalse';
			$question->questiontext = htmlspecialchars ( trim ( $rowdata [1] ), ENT_NOQUOTES );
			$answer = ( int ) ($rowdata [2]); // true =1,false=0;
			$question->generalfeedback = trim ( $rowdata [3] );
			$question->generalfeedbackformat = '1';
			$question->penalty = '1';
			$question->defaultmark = ( int ) ($rowdata [4]);
			
			if ($answer > 0) {
				$question->answer = true;
			} else {
				$question->answer = false;
			}
			$question->correctanswer = $question->answer;
			$question->fraction = 1;
			$question->feedbacktrue = $this->strToHTMLformat ( trim ( '' ) );
			$question->feedbackfalse = $this->strToHTMLformat ( trim ( '' ) );
			$questions [] = $question;
		}
		return $questions;
	}
	protected function strToHTMLformat($text) {
		return array (
				'text' => htmlspecialchars ( trim ( $text ), ENT_NOQUOTES ),
				'format' => FORMAT_HTML,
				'files' => array () 
		);
	}
	public function readquestion($lines) {
		// This is no longer needed but might still be called by default.php.
		return;
	}
	public function writequestion($question) {
		// no output ~
		return;
	}
}
