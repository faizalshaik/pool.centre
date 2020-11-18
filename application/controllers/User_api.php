<?php
defined('BASEPATH') or exit('No direct script access allowed');
header('Content-Type: application/json');

class User_api extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		date_default_timezone_set('Africa/Lagos');

		$this->load->model('Admin_model');
		$this->load->model('Base_model');
		$this->load->model('User_model');
		$this->load->model('Game_model');
		$this->load->model('Option_model');
		$this->load->model('UserOption_model');
		$this->load->model('Setting_model');
		$this->load->model('Terminal_model');
		$this->load->model('Week_model');
		$this->load->model('Prize_model');
		$this->load->model('FundRequest_model');
		$this->load->model('Bet_model');
		$this->load->model('Summary_model');
		$this->load->model('DeleteRequest_model');
		ini_set('memory_limit', '1024M'); // or you could use 1G		
	}


	public function test()
	{
		//srand(time()+3000);
		//echo rand(10000, 99999);
		// echo $curDate = date("Y-m-d H:i:s").'\n';

		// $date = new DateTime();
		// $date->add(new DateInterval('PT10H'));
		// echo $date->format('Y-m-d H:i:s');
		//$this->calcTerminalSummary(4,3,1,80,40,false);
		//$this->isSameBets($bets[0], $bets[1]);
		//$this->hhh($bets[0]);

		//echo $this->calc_line(array(3, 4, 5, 6), 10);
	}

	public function reply($result, $message, $data)
	{
		$result = array('result' => $result, 'message' => $message, 'data' => $data);
		echo json_encode($result);
	}

	private function checkLogin()
	{
		$userId = $this->input->post('user');
		if ($userId == "") {
			$this->reply(1002, "user required", null);
			return null;
		}

		$sn = $this->input->post('sn');
		if ($sn == "") {
			$this->reply(1002, "sn required", null);
			return null;
		}

		$token = $this->input->post('token');
		if ($token == "") {
			$this->reply(1002, "token required", null);
			return null;
		}

		$user = $this->User_model->getRow(array('user_id' => $userId));
		if ($user == null) {
			$this->reply(1002, "user does not exist", null);
			return null;
		}
		if ($user->status == 0) {
			$this->reply(1002, "user is not allowed", null);
			return null;
		}

		$terminal = $this->Terminal_model->getRow(array('terminal_no' => $sn));
		if ($terminal == null) {
			$this->reply(1002, "sn does not exist", null);
			return null;
		}
		if ($terminal->status == 0) {
			$this->reply(1002, "terminal is not allowed", null);
			return null;
		}

		if ($token != $user->token) {
			$this->reply(1002, "token mismatch", null);
			return null;
		}

		$user->terminal_id = $terminal->Id;
		$user->terminal_no = $terminal->terminal_no;
		return $user;
	}


	public function ping()
	{
		echo "1";
	}

	private function find_data($lst, $id)
	{
		foreach ($lst as $ele) {
			if ($ele->Id == $id) return $ele;
		}
		return null;
	}

	private function checkMissedGames($gamelists, $bet)
	{
		$missed = array();
		if ($bet['type'] == 'Group') {
			foreach ($bet['gamelist'] as $grp) {
				foreach ($grp['list'] as $gameNo) {
					$bExist = false;
					foreach ($gamelists as $game) {
						if ($game->game_no == $gameNo) {
							$bExist = true;
							break;
						}
					}
					if ($bExist == false) $missed[] = $gameNo;
				}
			}
		} else {
			foreach ($bet['gamelist'] as $gameNo) {
				$bExist = false;
				foreach ($gamelists as $game) {
					if ($game->game_no == $gameNo) {
						$bExist = true;
						break;
					}
				}
				if ($bExist == false) $missed[] = $gameNo;
			}
		}
		return $missed;
	}

	private function isSameBets($bet0, $bet1)
	{
		if ($bet0['type'] != $bet1['type']) return false;
		if ($bet0['option'] != $bet1['option']) return false;
		if (count($bet0['gamelist']) != count($bet1['gamelist'])) return false;

		$len = count($bet0['gamelist']);
		if ($bet0['type'] == 'Group') {
			for ($iGrp = 0; $iGrp < $len; $iGrp++) {
				$grp0 = $bet0['gamelist'][$iGrp];
				$grp1 = $bet1['gamelist'][$iGrp];

				if ($grp0['under'][0] != $grp1['under'][0]) return false;
				if (count($grp0['list']) != count($grp1['list'])) return false;

				$len1 = count($grp0['list']);
				for ($iGame = 0; $iGame < $len1; $iGame++) {
					if ($grp0['list'][$iGame] != $grp1['list'][$iGame]) return false;
				}
			}
		} else {
			for ($iGame = 0; $iGame < $len; $iGame++) {
				if ($bet0['gamelist'][$iGame] != $bet1['gamelist'][$iGame]) return false;
			}
		}
		return true;
	}


	private function calc_line($unders, $nGame)
	{
		$line = 0;
		foreach ($unders as $under) {
			$val = 	$nGame;
			$div = 1;
			for ($i = 2; $i <= $under; $i++) {
				$div *= $i;
				$val *= ($nGame - $i + 1);
			}
			$line += ($val / $div);
		}
		return $line;
	}

	private function calcLine($bet)
	{
		$line = 1;
		if ($bet['type'] == "Nap/Perm") {
			$line = $this->calc_line($bet['under'], count($bet['gamelist']));
		} else if ($bet['type'] == "Group") {
			foreach ($bet['gamelist'] as $grp) {
				$line *= $this->calc_line($grp['under'], count($grp['list']));
			}
		}
		return $line;
	}


	private function calcSummary($userId, $agent_id, $option, $commission, $week_no, $tag)
	{
		$summaryId = $userId . "_" . $tag . '_' . $option . '_' . $week_no;
		$sales = 0;
		$win = 0;
		$bets = null;
		$bets = $this->Bet_model->getDatas(array('user_id' => $userId, 'option_id' => $option, 'week' => $week_no, 'tag' => $tag, 'status' => 1));
		if (count($bets) == 0) {
			$this->Summary_model->deleteRow(array('summary_id' => $summaryId));
			return;
		}

		$terminalIds = array();
		foreach ($bets as $bet) {
			$sales += $bet->stake_amount;
			$win += $bet->won_amount;
			$terminalIds[$bet->terminal_id] = $bet->terminal_id;
		}

		$terminals = array();
		foreach ($terminalIds as $key => $val) $terminals[] = $key;

		$data = array(
			'summary_id' => $summaryId, 'user_id' => $userId, 'tag' => $tag, 'agent_id' => $agent_id,
			'option_id' => $option, 'commission' => $commission, 'week_no' => $week_no,
			'sales' => $sales, 'win' => $win, 'payable' => $sales * $commission / 100, 'terminals' => join(',', $terminals)
		);

		$row = $this->Summary_model->getRow(array('summary_id' => $summaryId));

		if ($row) {
			$this->Summary_model->updateData(array('Id' => $row->Id), $data);
		} else {
			$this->Summary_model->insertData($data);
		}
	}

	private function generateRandomString($length = 10)
	{
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = strlen($characters);
		$randomString = '';
		for ($i = 0; $i < $length; $i++) {
			$randomString .= $characters[rand(0, $charactersLength - 1)];
		}
		return $randomString;
	}

	public function login()
	{
		$_POST = json_decode(file_get_contents('php://input'), true);
		$username = $this->input->post('user');
		if ($username == "") return $this->reply(1001, "username required", null);

		$sn = $this->input->post('sn');
		if ($sn == "") return $this->reply(1001, "sn required", null);
		$password = $this->input->post('password');
		if ($password == "") return $this->reply(1001, "password required", null);

		$terminal = $this->Terminal_model->getRow(array('terminal_no' => $sn));
		if ($terminal == null) return $this->reply(1002, "sn dose not exist", null);
		if ($terminal->status != 1) return $this->reply(1002, "terminal is not allowed", null);

		$user = $this->User_model->getRow(array('user_id' => $username));
		if ($user == null) return $this->reply(1002, "user dose not exist", null);
		if ($user->status != 1) return $this->reply(1002, "user is not allowed", null);
		if ($user->password != $password) return $this->reply(1002, "wrong password", null);

		$token = $this->generateRandomString(32);
		$this->User_model->updateData(array('Id' => $user->Id), array('token' => $token));

		$options = array();
		$datas = $this->UserOption_model->getDatas(array('user_id' => $user->Id, 'status' => 1));
		foreach ($datas as $data) {
			$row = $this->Option_model->getRow(array('Id' => $data->option_id));
			if ($row == null) continue;
			$options[] = $row->name;
		}

		$unders = array();
		if ($user->unders & 1) $unders[] = "U3";
		if ($user->unders & 2) $unders[] = "U4";
		if ($user->unders & 4) $unders[] = "U5";
		if ($user->unders & 8) $unders[] = "U6";

		$tags = array();
		if ($user->tags & 1) $tags[] = "1";
		if ($user->tags & 2) $tags[] = "2";
		if ($user->tags & 4) $tags[] = "3";
		if ($user->tags & 8) $tags[] = "4";
		if ($user->tags & 16) $tags[] = "5";

		$curWeekNo = $this->Setting_model->getCurrentWeekNo();
		if ($curWeekNo == 0) return $this->reply(-1, "no current weekno", null);

		$curWeek = $this->Week_model->getRow(array('week_no' => $curWeekNo));
		if ($curWeek == null) return $this->reply(-1, "week does not exist.", null);

		$games = array();
		$datas = $this->Game_model->getDatas(array('week_no' => $curWeekNo, 'status' => 1), "game_no");
		foreach ($datas as $data) {
			$games[] = $data->game_no;
		}

		$this->reply(1, "success", array(
			'sn' => $sn,
			'token' => $token,
			'default_type' => $terminal->default_type,
			'default_sort' => $terminal->default_option,
			'default_under' => $terminal->default_under,
			'possible_sort' => $options,
			'possible_under' => $unders,
			'possible_tag' => $tags,
			'games' => $games,
			'week' => $curWeekNo,
			'start_at' => $curWeek->start_at,
			'close_at' => $curWeek->close_at,
			'validity' => $curWeek->validity,
			'void_bet' => $curWeek->void_bet,
			'credit_limit' => $terminal->credit_limit
		));
	}

	public function reset()
	{
		$_POST = json_decode(file_get_contents('php://input'), true);
		$user = $this->checkLogin();
		if ($user == null) return;

		$options = array();
		$datas = $this->UserOption_model->getDatas(array('user_id' => $user->Id, 'status' => 1));
		foreach ($datas as $data) {
			$row = $this->Option_model->getRow(array('Id' => $data->option_id));
			if ($row == null) continue;
			$options[] = $row->name;
		}

		$unders = array();
		if ($user->unders & 1) $unders[] = "U3";
		if ($user->unders & 2) $unders[] = "U4";
		if ($user->unders & 4) $unders[] = "U5";
		if ($user->unders & 8) $unders[] = "U6";

		$curWeekNo = $this->Setting_model->getCurrentWeekNo();
		if ($curWeekNo == 0) return $this->reply(-1, "no current weekno", null);

		$curWeek = $this->Week_model->getRow(array('week_no' => $curWeekNo));
		if ($curWeek == null) return $this->reply(-1, "week does not exist.", null);

		$games = array();
		$datas = $this->Game_model->getDatas(array('week_no' => $curWeekNo, 'status' => 1), "game_no");
		foreach ($datas as $data) {
			$games[] = $data->game_no;
		}

		$this->reply(1, "success", array(
			'sn' => $user->terminal_no,
			'token' => $user->token,
			'default_type' => $user->default_type,
			'default_sort' => $user->default_option,
			'default_under' => $user->default_under,
			'possible_sort' => $options,
			'possible_under' => $unders,
			'games' => $games,
			'week' => $curWeekNo,
			'start_at' => $curWeek->start_at,
			'close_at' => $curWeek->close_at,
			'validity' => $curWeek->validity,
			'void_bet' => $curWeek->void_bet,
			'credit_limit' => $user->credit_limit
		));
	}

	public function make_bet()
	{
		$_POST = json_decode(file_get_contents('php://input'), true);
		$user = $this->checkLogin();
		if ($user == null)
			return;

		$reqBets = (object) $this->input->post('bets');

		//return $this->reply(-1, "text process1", null);		
		$agent = $this->User_model->getRow(array('Id' => $user->agent_id));
		if ($agent == null)
			return $this->reply(-1, "no agent", null);

		//check current week
		$curWeekNo = $this->Setting_model->getCurrentWeekNo();
		if ($curWeekNo == 0)
			return $this->reply(-1, "no current week_no", null);
		$curWeek = $this->Week_model->getRow(array('week_no' => $curWeekNo));
		if ($curWeek == null)
			return $this->reply(-1, "no current week_no", null);

		$curDate = date("Y-m-d H:i:s");
		if ($curWeek->start_at > $curDate || $curWeek->close_at < $curDate)
			return $this->reply(-1, "coming soon", null);
		//return $this->reply(-1, "text process2", null);


		//generate ticket no		
		srand(time() + $user->Id);
		$ticketRealNo = substr($user->last_ticket_no, strlen($user->user_id));
		if($ticketRealNo < 10000)$ticketRealNo = rand(10000, 19999);
		if($ticketRealNo >=99999) $ticketRealNo = rand(10000, 19999);
		$ticketRealNo ++;
		$ticketNo = $user->user_id . $ticketRealNo;

		$betIdBase = $ticketRealNo.($user->Id + 1000);

		//get gamelist
		$gamelists = $this->Game_model->getDatas(array('week_no' => $curWeekNo, 'status' => 1));
		$bets = $this->Bet_model->getBets(array('user_id' => $user->Id, 'week' => $curWeekNo));
		//return $this->reply(-1, "text process3", null);

		//check betting
		$resultBets = array();
		$betIdx = 0;

		foreach ($reqBets as $bet) {
			if (!isset($bet['option'])) $bet['option'] = $user->default_option;
			if (!isset($bet['under'])) $bet['under'] = array($user->default_under[1]);

			//get old bets
			// $bets = $this->Bet_model->getBets(array('user_id'=>$user->Id, 'week'=>$curWeekNo, 'tag'=>$bet['tag']));

			//check missed games			
			$missed = $this->checkMissedGames($gamelists, $bet);
			//return $this->reply(-1, "text process33", null);
			if (count($missed) > 0) {
				$resultBets[] = array(
					'result' => 1004,
					'message' => implode(' ', $missed) . ' :mismatch games',
					'type' => $bet['type'],
					'option' => $bet['option'],
					'under' => $bet['under'],
					'gamelist' => $bet['gamelist'],
					'stake_amount' => $bet['stake_amount']
				);
				continue;
			}

			//check stake
			if ($bet['stake_amount'] < $user->min_stake) {
				$resultBets[] = array(
					'result' => 1004,
					'message' => 'stake amount is less than min_stake',
					'type' => $bet['type'],
					'option' => $bet['option'],
					'under' => $bet['under'],
					'gamelist' => $bet['gamelist'],
					'stake_amount' => $bet['stake_amount']
				);
				continue;
			}

			if ($bet['stake_amount'] > $user->max_stake) {
				$resultBets[] = array(
					'result' => 1004,
					'message' => 'stake amount is greater than max_stake',
					'type' => $bet['type'],
					'option' => $bet['option'],
					'under' => $bet['under'],
					'gamelist' => $bet['gamelist'],
					'stake_amount' => $bet['stake_amount']
				);
				continue;
			}

			if ($bet['stake_amount'] > $user->credit_limit) {
				$resultBets[] = array(
					'result'=>1004,
					'message'=>'credit lack',
					'type'=>$bet['type'],
					'option'=>$bet['option'],
					'under'=>$bet['under'],
					'gamelist'=>$bet['gamelist'],
					'stake_amount'=>$bet['stake_amount']	
				);
				continue;	
			}
			//return $this->reply(-1, "text process4", $bet);

			//stake check again include old bets
			$totalStake = 0;
			foreach ($bets as $oldBet) {
				if ($this->isSameBets($oldBet, $bet))
					$totalStake += $oldBet['stake_amount'];
			}
			//return $this->reply(-1, "text process44", $totalStake);
			//return;

			if ($totalStake > 0 && ($totalStake + $bet['stake_amount']) > $user->max_stake) {
				$resultBets[] = array(
					'result' => 1004,
					'message' => 'stake amount is greater than max_stake',
					'type' => $bet['type'],
					'option' => $bet['option'],
					'under' => $bet['under'],
					'gamelist' => $bet['gamelist'],
					'stake_amount' => $bet['stake_amount']
				);
				continue;
			}
			//return $this->reply(-1, "text process5", $bet);

			//line calc
			$line = $this->calcLine($bet);
			if ($line == 0) {
				$resultBets[] = array(
					'result' => 1003,
					'message' => 'apl is zero',
					'type' => $bet['type'],
					'option' => $bet['option'],
					'under' => $bet['under'],
					'gamelist' => $bet['gamelist'],
					'stake_amount' => $bet['stake_amount']
				);
				continue;
			}

			$betIdx++;			
			//make new bet
			$newBet = array(
				//'bet_id' => $user->user_id . rand(100000, 999999),
				//'bet_id' => $user->user_id . $ticketRealNo . $betIdx,
				'bet_id' => $betIdBase + $betIdx,
				'bet_time' => $curDate,
				'ticket_no' => $ticketNo,
				'user_id' => $user->Id,
				'tag' => $bet['tag'],
				'terminal_id' => $user->terminal_id,
				'agent_id' => $user->agent_id,
				'stake_amount' => $bet['stake_amount'],
				'gamelist' => $bet['gamelist'],
				'week' => $curWeekNo,
				'under' => $bet['under'],
				'option' => $bet['option'],
				'type' => $bet['type'],
				'apl' => $bet['stake_amount'] / $line,
			);

			//save new bet
			$option = $this->Option_model->getRow(array('name' => $bet['option']));
			$this->Bet_model->addNewBet($user->Id, $user->terminal_id, $option,  $newBet);

			$repeatCond = array(
				'week' => $curWeekNo,
				'type' => $bet['type'], 'option_id' => $option->Id, 'gamelist' => json_encode($bet['gamelist'])
			);
			$count = $this->Bet_model->getCounts($repeatCond);
			$this->Bet_model->updateData($repeatCond, array('repeats' => $count));

			$commission = 0;
			if ($option != null) $commission = $option->commision;
			$this->calcSummary($user->Id, $user->agent_id, $option->Id, $commission, $curWeekNo, $bet['tag']);
			//return $this->reply(-1, "text process6", $bet);

			//reduce user stake
			$user->credit_limit -= $bet['stake_amount'];
			$this->User_model->updateData(array('Id'=>$user->Id), array('credit_limit'=>$user->credit_limit));


			$bets[] = $newBet;
			$resultBets[] = array(
				'result' => 1,
				'message' => 'success',
				'type' => $bet['type'],
				'option' => $bet['option'],
				'under' => $bet['under'],
				'tag' => $bet['tag'],
				'gamelist' => $bet['gamelist'],
				'stake_amount' => $bet['stake_amount'],
				'bet_id' => $newBet['bet_id'],
				'apl' => $newBet['apl']
			);
		}

		$this->User_model->updateData(array('Id' => $user->Id), array('last_ticket_no' => $ticketNo));
		$this->reply(1, "success", array(
			'ticket_no' => $ticketRealNo,
			'bet_time' => $curDate,
			'week' => $curWeekNo,
			'agent_id' => $agent->user_id,
			'user_id' => $user->user_id,
			'user_name' => $user->email,
			'zone_name' => $agent->email,
			'terminal_id' => $user->terminal_no,
			'bets' => $resultBets
		));
	}

	public function results()
	{
		$_POST = json_decode(file_get_contents('php://input'), true);

		$user = $this->checkLogin();
		if ($user == null) return;

		$curWeekNo = $this->input->post('week');
		if ($curWeekNo == 0 || $curWeekNo == "") {
			$curWeekNo = $this->Setting_model->getCurrentWeekNo();
			if ($curWeekNo == 0) return $this->reply(-1, "no current weekno", null);
		}

		$games = array();
		$datas = $this->Game_model->getDatas(array('week_no' => $curWeekNo, 'status' => 1, 'checked' => 1), "game_no");
		foreach ($datas as $data) {
			$games[] = $data->game_no;
		}
		$this->reply(1, "success", array('week' => $curWeekNo, 'drawn' => $games));
	}

	public function reprint()
	{
		$_POST = json_decode(file_get_contents('php://input'), true);

		$user = $this->checkLogin();
		if ($user == null) return;

		$ticketNo = $this->input->post('ticket_no');
		if ($ticketNo == "" || $ticketNo==0)
			$ticketNo = $user->last_ticket_no;
		else
		{
		    //POS always send user_id + ticketno
			if(strlen($ticketNo) <=5)
			    $ticketNo = $user->user_id . $ticketNo;
		}

		if($ticketNo=="")
		{
			return $this->reply(1002, "ticket_no required", null);
		}

		$bets = $this->Bet_model->getBets(array('ticket_no' => $ticketNo));
		if (count($bets) == 0) 
		    return $this->reply(1002, "ticket_no dose not exist", null);

		$usr = $this->User_model->getRow(array('Id' => $bets[0]['user_id']));
		if ($usr == null) return $this->reply(1002, "user dose not exist", null);

		$agentId = "";
		$agent = $this->User_model->getRow(array('Id' => $user->agent_id));
		if ($agent != null) $agentId = $agent->user_id;

		$this->reply(1, "success", array(
			//'ticket_no' => $bets[0]['ticket_no'],
			'ticket_no' => substr($ticketNo, strlen($user->user_id)),			
			'bet_time' => $bets[0]['bet_time'],
			'week' => $bets[0]['week'],
			'terminal_id' => $user->terminal_no,
			'agent_id' => $agentId,
			'user_id' => $user->user_id,
			'user_name' => $user->email,
			'zone_name' => $agent->email,
			'bets' => $bets
		));
	}

	public function win_list()
	{
		$_POST = json_decode(file_get_contents('php://input'), true);

		$user = $this->checkLogin();
		if ($user == null) return;

		$current_page = $this->input->post('current_page');
		if ($current_page == null || $current_page == 0)
			$current_page = 1;

		$curWeekNo = $this->input->post('week');
		if ($curWeekNo == 0 || $curWeekNo == '') $curWeekNo = $this->Setting_model->getCurrentWeekNo();
		if ($curWeekNo == 0) return $this->reply(-1, 'no current weekno', null);
		$options = $this->Option_model->getDatas(null);


		$cond = array('user_id' => $user->Id, 'win_result' => 'Win', 'week' => $curWeekNo, 'tag' => 1, 'status' => 1);
		$ticketNo = $this->input->post('ticket_no');
		if ($ticketNo != "") $cond['ticket_no'] = $user->user_id . $ticketNo;
		$win_list = array();

		$date = "";

		$totalWin = 0;
		$totalCnt = 0;
		for ($tag = 1; $tag <= 5; $tag++) {
			$cond['tag'] = $tag;
			$bets = $this->Bet_model->getBets($cond);
			if (count($bets) == 0) continue;

			$wins = array();
			foreach ($bets as $bet) {
				$option = $this->find_data($options, $bet['option_id']);
				if ($option == null) continue;
				if ($date == "")
					$date = $bet['bet_time'];
				$wins[] = array(
					'bet_id' => $bet['bet_id'], 'under' => $bet['under'],
					'option' => $option->name, 'tag' => $bet['tag'],
					'type' => $bet['type'], 'gamelist' => $bet['gamelist'],
					'apl' => $bet['apl'],
					'sale' => $bet['stake_amount'], 'win' => number_format($bet['won_amount'], 2),
					'bet_time' => $bet['bet_time']
				);
				$totalWin += $bet['won_amount'];
			}
			$totalCnt += count($wins);
			$win_list[] = array('tag' => $tag, 'bets' => $wins);
		}

		$perCnt = 12;
		//page filter
		$totalPages = (int) ($totalCnt / $perCnt);
		if ($totalCnt % $perCnt >= 1)  $totalPages++;

		$startIdx = ($current_page - 1) * $perCnt;
		$curIdx = -1;
		$retLst = array();
		foreach ($win_list as $wintag) {
			$bets = array();
			foreach ($wintag['bets'] as $bet) {
				$curIdx++;
				if ($curIdx < $startIdx) continue;
				$bets[] = $bet;

				if ($curIdx >= $startIdx + $perCnt - 1) break;
			}
			if (count($bets) > 0) {
				$retLst[] = array('tag' => $wintag['tag'], 'bets' => $bets);
			}

			if ($curIdx >= $startIdx + $perCnt - 1) break;
		}



		$agentId = "";
		$agent = $this->User_model->getRow(array('Id' => $user->agent_id));
		if ($agent != null) $agentId = $agent->user_id;

		$this->reply(1, "success", array(
			'week' => $curWeekNo,
			'agent_id' => $agentId,
			'user_id' => $user->user_id,
			'user_name' => $user->email,
			'zone_name' => $agent->email,
			'date' => $date,
			//'win_tag_list'=>$win_list,
			'win_tag_list' => $retLst,
			'current_page' => $current_page,
			'total_page' => $totalPages,
			'total_win' => number_format($totalWin, 2)
		));
	}



	public function report()
	{
		$_POST = json_decode(file_get_contents('php://input'), true);

		$user = $this->checkLogin();
		if ($user == null) return;

		$curWeekNo = $this->input->post('week');
		if ($curWeekNo == 0 || $curWeekNo == "") $curWeekNo = $this->Setting_model->getCurrentWeekNo();
		if ($curWeekNo == 0) return $this->reply(-1, 'no current weekno', null);

		$curWeek = $this->Week_model->getRow(array('week_no' => $curWeekNo));
		if ($curWeek == null) return $this->reply(-1, 'week does not exist.', null);

		$summaries = $this->Summary_model->getDatas(array('week_no' => $curWeekNo, 'user_id' => $user->Id), 'tag');
		$options = 	$this->Option_model->getDatas(null);

		$total_sale = 0;
		$total_win = 0;

		$total_summary = array();
		$tag_summary = array();
		foreach ($summaries as $summary) {
			$option = $this->find_data($options, $summary->option_id);
			if ($option == null) continue;
			$total_sale += $summary->sales;
			$total_win += $summary->win;

			$totalEntry = null;
			if (isset($total_summary[$option->Id]))
				$totalEntry = $total_summary[$option->Id];
			else $totalEntry = array('option' => $option->name, 'win' => 0, 'sale' => 0);

			$totalEntry['win'] += $summary->win;
			$totalEntry['sale'] += $summary->sales;
			$total_summary[$option->Id] = $totalEntry;

			$tagEntry = null;
			if (isset($tag_summary[$summary->tag]))
				$tagEntry = $tag_summary[$summary->tag];
			else $tagEntry = array();

			$optEntry = null;
			if (isset($tagEntry[$option->Id]))
				$optEntry = $tagEntry[$option->Id];
			else $optEntry = array('option' => $option->name, 'win' => 0, 'sale' => 0);

			$optEntry['win'] += $summary->win;
			$optEntry['sale'] += $summary->sales;
			$tagEntry[$option->Id] = $optEntry;

			$tag_summary[$summary->tag] = $tagEntry;
		}


		$agentId = "";
		$agent = $this->User_model->getRow(array('Id' => $user->agent_id));
		if ($agent != null) $agentId = $agent->user_id;

		$totalSummary = array();
		foreach ($total_summary as $key => $totalEntry) {
			$totalEntry['sale'] = number_format($totalEntry['sale'], 2);
			$totalEntry['win'] = number_format($totalEntry['win'], 2);
			$totalSummary[] = $totalEntry;
		}

		$tagSummary = array();
		foreach ($tag_summary as $tag => $taglEntry) {
			$odds = array();
			foreach ($taglEntry as $key => $oddEntry) {
				$oddEntry['win'] = number_format($oddEntry['win'], 2);
				$oddEntry['sale'] = number_format($oddEntry['sale'], 2);
				$odds[] = $oddEntry;
			}
			$tagSummary[] = array('tag' => $tag, 'odds' => $odds);
		}

		$this->reply(1, 'success', array(
			'week' => $curWeekNo,
			'agent_id' => $agentId,
			'user_id' => $user->user_id,
			'user_name' => $user->email,
			'zone_name' => $agent->email,
			'user_summary' => array(
				'odds' => $totalSummary,
				'total_sale' => number_format($total_sale, 2),
				'total_win' => number_format($total_win, 2),
			),
			'tag_summary' => $tagSummary,
			'close_at' => $curWeek->close_at
		));
	}

	public function credit_limit()
	{
		$_POST = json_decode(file_get_contents('php://input'), true);

		$user = $this->checkLogin();
		if ($user == null) return;
		$this->reply(1, 'success', array('credit_limit' => $user->credit_limit));
	}

	public function logout()
	{
		$_POST = json_decode(file_get_contents('php://input'), true);
		$user = $this->checkLogin();
		if ($user == null) return;
		$token = $this->generateRandomString(32);
		$this->User_model->updateData(array('Id' => $user->Id), array('token' => $token));
		$this->reply(1, 'success', null);
	}

	public function void_bet()
	{
		$_POST = json_decode(file_get_contents('php://input'), true);
		$user = $this->checkLogin();
		if ($user == null) return;

		//check user
		$betId = $this->input->post('bet_id');

		$bets = $this->Bet_model->getBets(array('bet_id' => $betId, 'user_id' => $user->Id));
		if (count($bets) == 0) {
			$bets = $this->Bet_model->getBets(array('ticket_no' => $user->user_id.$betId, 'user_id' => $user->Id));
			if (count($bets) == 0)
				return $this->reply(-1, "bet_id, tsn dose not exist", null);
		}


		foreach ($bets as $bet) {
			// if ($bet['user_id'] != $user->Id)
			// 	return $this->reply(-1, "user hasn't bet", null);

			$week = $this->Week_model->getRow(array('week_no' => $bet['week']));
			if ($week == null)
				return $this->reply(-1, "week dose not exist", null);

			$curDate = new DateTime();
			if ($curDate->format('Y-m-d H:i:s') > $week->close_at)
				return $this->reply(1004, "bet does not change in past week", null);

			$curDate->sub(new DateInterval('PT' . $week->void_bet . 'H'));
			if ($curDate->format('Y-m-d H:i:s') > $bet['bet_time'])
				return $this->reply(1003, "void time passed", null);

			$gamelists = $this->Game_model->getDatas(array('week_no' => $week->week_no, 'status' => 1));
			if ($gamelists == null)
				return $this->reply(-1, "game does not exist", null);

			$missed = $this->checkMissedGames($gamelists, $bet);
			if (count($missed) > 0)
				return $this->reply(1003, "void failed", null);

			//save deelte request
			$row = $this->DeleteRequest_model->getRow(array('bet_id' => $bet['Id'], 'user_id' => $user->Id));
			// if ($row != null)
			// 	return $this->reply(1003, "already requested", null);
			if($row==null)
				$this->DeleteRequest_model->insertData(array('bet_id' => $bet['Id'], 'user_id' => $user->Id, 'terminal_id' => $user->terminal_id, 'agent_id' => $user->agent_id));

			//update bet status
			$this->Bet_model->updateData(array('Id' => $bet['Id']), array('status' => 2));


			$commision = 0;
			$opt = 	$this->UserOption_model->getRow(array('user_id' => $user->Id, 'option_id' => $bet['option_id']));
			if ($opt != null) $commision = $opt->commision;

			//return $this->reply(1003, "kkk", null);															

			$this->calcSummary($user->Id, $user->agent_id, $bet['option_id'], $commision, $bet['week'], $bet['tag']);
		}
		return $this->reply(1, "success", null);
	}

	public function password_change()
	{
		$_POST = json_decode(file_get_contents('php://input'), true);

		$user = $this->checkLogin();
		if ($user == null) return;

		$newPasswd = $this->input->post('new_password');
		if ($newPasswd == "")
			return $this->reply(1001, 'enter new password', null);
		$this->User_model->updateData(array('Id' => $user->Id), array('password' => $newPasswd));
		return $this->reply(1, 'success', null);
	}

	public function void_list()
	{
		$_POST = json_decode(file_get_contents('php://input'), true);

		$user = $this->checkLogin();
		if ($user == null) return;

		$curWeekNo = $this->input->post('week');
		if ($curWeekNo == 0) $curWeekNo = $this->Setting_model->getCurrentWeekNo();
		if ($curWeekNo == 0) return $this->reply(-1, 'no current weekno', null);

		$bets = $this->Bet_model->getBets(array('user_id' => $user->Id, 'week' => $curWeekNo, 'status' => 2));

		$agentId = "";
		$agent = $this->User_model->getRow(array('Id' => $user->agent_id));
		if ($agent != null) $agentId = $agent->user_id;

		$results = array();
		foreach ($bets as $bet) {
			$results[] = array('bet_id' => $bet['bet_id'], 'stake_amount' => $bet['stake_amount']);
		}
		$this->reply(1, 'success', array('week' => $curWeekNo, 'agent_id' => $agentId, 'void_list' => $results));
	}

	public function search()
	{
		$_POST = json_decode(file_get_contents('php://input'), true);

		$user = $this->checkLogin();
		if ($user == null) return;
		$curWeekNo = $this->input->post('week');
		if ($curWeekNo == 0) $curWeekNo = $this->Setting_model->getCurrentWeekNo();
		if ($curWeekNo == 0) return $this->reply(-1, 'no current weekno', null);

		$searchWord = $this->input->post('searchword');
		if ($searchWord == "") return $this->reply(-1, 'no search word', null);


		$isTicket = $this->input->post('is_ticketid');

		$cond = array('user_id' => $user->Id, 'week' => $curWeekNo);
		// if($isTicket==1)$cond['ticket_no'] = $searchWord;
		// else $cond['bet_id'] = $searchWord;

		$restBets = array();
		$bets = $this->Bet_model->getBets($cond);
		for ($i = 0; $i < count($bets); $i++) {
			if ($isTicket == 1) {
				if (stristr($bets[$i]['ticket_no'], $searchWord) === FALSE) continue;
			} else if (stristr($bets[$i]['bet_id'], $searchWord) === FALSE) continue;

			if ($bets[$i]['win_result'] == '') {
				unset($bets[$i]['win_result']);
				unset($bets[$i]['won_amount']);
			}

			if ($bets[$i]['status'] == 2)
				$bets[$i]['status'] = 'Void';
			else {
				if ($bets[$i]['win_result'] == '')
					$bets[$i]['status'] = 'Active';
				else {
					$bets[$i]['status'] = $bets[$i]['win_result'];
				}
			}

			$restBets[] = $bets[$i];
			if (count($restBets) >= 10) break;
		}
		$this->reply(1, 'success', array('week' => $curWeekNo, 'search_result' => $restBets));
	}

	public function ticket_list()
	{
		$_POST = json_decode(file_get_contents('php://input'), true);

		$user = $this->checkLogin();
		if ($user == null) return;
		$curWeekNo = $this->input->post('week');
		$current_page = $this->input->post('current_page');
		if ($current_page == null || $current_page == 0)
			$current_page = 1;

		if ($curWeekNo == 0) $curWeekNo = $this->Setting_model->getCurrentWeekNo();
		if ($curWeekNo == 0) return $this->reply(-1, 'no current weekno', null);

		$bets = $this->Bet_model->getDatas(array('user_id' => $user->Id, 'week' => $curWeekNo, 'status' => 1), 'bet_time', 'DESC');
		$ticketList = array();
		foreach ($bets as $bet) {
			if(substr($bet->ticket_no, 0, strlen($user->user_id))==$user->user_id)
				$ticketList[substr($bet->ticket_no, strlen($user->user_id))] = 1;
			else
				$ticketList[$bet->ticket_no] = 1;
		}

		$totalCnt = count($ticketList);
		$perCnt = 20;
		$totalPage = $totalCnt / $perCnt;
		if ($totalCnt % $perCnt) $totalPage++;

		$startIdx = ($current_page - 1) * $perCnt;
		$curIdx = -1;

		$tickets = array();
		foreach ($ticketList as $tickno => $val) {
			$curIdx++;
			if ($curIdx < $startIdx) continue;
			$tickets[] = $tickno;
			if ($curIdx >= $startIdx + $perCnt - 1) break;
		}
		$this->reply(1, 'success', array('total_page' => $totalPage, 'ticket_list' => $tickets,));
	}


	public function bet_counts()
	{
		$_POST = json_decode(file_get_contents('php://input'), true);

		$user = $this->checkLogin();
		if ($user == null) return;

		$curWeekNo = $this->input->post('week');
		if ($curWeekNo == 0) $curWeekNo = $this->Setting_model->getCurrentWeekNo();
		if ($curWeekNo == 0) return $this->reply(-1, 'no current weekno', null);

		$counts = $this->Bet_model->getCounts(array('user_id' => $user->Id, 'week' => $curWeekNo, 'status' => 1));
		$agent = $this->User_model->getRow(array('Id' => $user->agent_id));
		if ($agent != null) $agentId = $agent->user_id;

		$this->reply(1, 'success', array(
			'week' => $curWeekNo,
			'bets_counts' => number_format($counts),
			'user_id' => $user->user_id,
			'user_name' => $user->email,
			'zone_name' => $agent->email
		));
	}
}
