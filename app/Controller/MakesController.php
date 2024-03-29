<?php
App::uses('AppController', 'Controller');
class MakesController extends AppController {
	public $components = array('Session', 'Flash');
	public $uses = array(
		'User','Knowledge','Template','Question','Activity','Category',
		'TargetKnowledge','GeneratedQuestion','GeneratedWord','PastExam',
	);
    public function beforeFilter(){
        $this->layout = 'experiment';
    }
	public function index(){
	}

	public function inputWord(){
		$this->loadModel('User');
		$themes = $this->User->find('first',array(
			'conditions' => array('User.id' => $this->Auth->user('id')),
			'fields' => array('User.theme_1','User.theme_2','User.theme_3'),
		));
		$themes = $themes['User'];
		$this->set(compact('themes'));
	}

	public function showKeywords(){
		if(empty($this->request->data['Make']['input_word']))
			return $this->redirect(array('action' => 'inputWord'));	

		$word = $this->request->data['Make']['input_word'];
		$keywords = $this->Knowledge->fetchKeywordFromInput($word);
		// $this->storeActivity("問題作成機能｜キーワード入力による検索->入力：".$word."｜");
		
		if(!isset($keywords)){
			$this->Session->setFlash(__(
				'検索結果が 0件であったため、検索画面に戻ります'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-danger'
			));
			$this->redirect(array('controller'=>'makes','action'=>'inputWord'));
		}else{
			// $this->storeActivity("問題作成機能｜キーワード一覧｜");
			$this->set(compact('keywords'));
		}
	}

	public function generateQuestions($tk_word){
		$this->autoRender = false;

		if(empty($tk_word))
			return $this->redirect(array('action' => 'selectMode'));

		$used_flg = $this->TargetKnowledge->isUsed($tk_word);
		$tk_id = $this->TargetKnowledge->fetchId($tk_word);

		// $this->storeActivity("問題作成機能｜キーワード一覧->選択：".$tk_word."｜");

		if($used_flg === false){
			// 問題生成
			$knowledge 	= $this->Knowledge->loadKnowledge($tk_word);
			$tk_list 	= $this->TargetKnowledge->fetchTargetKnowledgeList();
			$questions 	= $this->Template->generateQuestions($knowledge,$tk_list);
			
			// 生成問題登録
			$this->GeneratedQuestion->storeQuestions($questions,$tk_id);

			// 生成済みフラグをtrueへ
			$this->TargetKnowledge->toUsed($tk_id);
		}
		$this->redirect(array(
			'controller'=>'makes',
			'action'=>'showQuestions',$tk_word
		));
		
	}

	public function showQuestions($tk_word){
		if(empty($tk_word))
			return $this->redirect(array('action' => 'inputWord'));

		$tk_id = $this->TargetKnowledge->fetchId($tk_word);
		// テンプレートで絞込している箇所
		$generated_questions = $this->GeneratedQuestion->fetchQuestions($tk_id);
		
		$exam_id_list = $this->Knowledge->fetchExamId($tk_word);
		$exam_questions = $this->PastExam->fetchQuestions($exam_id_list);

		$this->set(compact('generated_questions','exam_questions','tk_word'));
	}

	public function editQuestion($question_id){
		$question = $this->GeneratedQuestion->findById($question_id);
		$this->set(compact('question','question_id'));
	}

	public function storeQuestion(){
		if ($this->request->is('post')) {
			$this->loadModel('MadeProblem');
			$this->request->data['MadeProblem']['user_id'] = $this->Auth->user('id');
			if($this->MadeProblem->save($this->request->data)){
				$this->Session->setFlash(__(
					'登録完了しました．'), 'alert', array(
						'plugin' => 'BoostCake',
						'class' => 'alert-success'
				));
				return $this->redirect(array('action' => 'showMadeQuestions'));
			}else{
				$this->Session->setFlash(__(
					'登録失敗しました．やり直しましょう．'), 'alert', array(
						'plugin' => 'BoostCake',
						'class' => 'alert-danger'
				));
			}
		}
	}

	public function makeManualQuestion(){
		if ($this->request->is('post')) {
			$this->loadModel('MadeProblem');
			$this->MadeProblem->create();
			$this->request->data['MadeProblem']['user_id'] = $this->Auth->user('id');
			if ($this->MadeProblem->save($this->request->data)) {
				$this->Session->setFlash(__(
					'登録完了しました．'), 'alert', array(
						'plugin' => 'BoostCake',
						'class' => 'alert-success'
				));
				return $this->redirect(array('action' => 'showMadeQuestions'));
			} else {
				$this->Session->setFlash(__(
					'登録失敗しました．やり直しましょう．'), 'alert', array(
						'plugin' => 'BoostCake',
						'class' => 'alert-danger'
				));
			}
		}
		$this->loadModel('User');
		$themes = $this->User->find('first',array(
			'conditions' => array('User.id' => $this->Auth->user('id')),
			'fields' => array('User.theme_1','User.theme_2','User.theme_3'),
		));
		$themes = $themes['User'];
		$this->set(compact('themes'));
	}

	public function showMadeQuestions(){
		$this->loadModel('MadeProblem');
		$made_problems = $this->MadeProblem->find('all',array(
			'conditions' => array('MadeProblem.user_id' => $this->Auth->user('id')),
		));
		$this->loadModel('User');
		$assigned_team = $this->User->find('first',array(
			'conditions' => array('User.id' => $this->Auth->user('id')),
			'fields' => array('User.team'),
		));

		if($assigned_team['User']['team'] === 'A'){
			$make_url = 'inputWord';
		}else if($assigned_team['User']['team'] === 'B'){
			$make_url = 'makeManualQuestion';
		}else{
			$this->Session->setFlash(__(
				'チームの割振りが済んでません．もう少々お待ち下さい．'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-danger'
			));
			return $this->redirect(array('controller'=>'users','action' => 'top'));
		}
		$themes = $this->User->find('first',array(
			'conditions' => array('User.id' => $this->Auth->user('id')),
			'fields' => array('User.theme_1','User.theme_2','User.theme_3'),
		));
		$themes = $themes['User'];
		if(($themes['theme_1'] === "")&&($themes['theme_2'] === "")&&($themes['theme_3'] === "")){
			$this->Session->setFlash(__(
				'テーマの割振りが済んでません．もう少々お待ち下さい．'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-danger'
			));
			return $this->redirect(array('controller'=>'users','action' => 'top'));
		}

		$made_problems = Set::extract('/MadeProblem/.',$made_problems);
		$this->set(compact('made_problems','make_url','themes'));
	}

    public function editMadeQuestion($problem_id){
		$this->loadModel('MadeProblem');
		if ($this->request->is('post')) {
			$this->MadeProblem->create();
			if ($this->MadeProblem->save($this->request->data)) {
				$this->Session->setFlash(__(
					'更新されました．'), 'alert', array(
						'plugin' => 'BoostCake',
						'class' => 'alert-success'
				));
				return $this->redirect(array('action' => 'showMadeQuestions'));
			} else {
				$this->Session->setFlash(__(
					'更新失敗しました．やり直してください．'), 'alert', array(
						'plugin' => 'BoostCake',
						'class' => 'alert-danger'
				));
			}
		}
		$problem = $this->MadeProblem->findById($problem_id);

		$this->loadModel('User');
		$themes = $this->User->find('first',array(
			'conditions' => array('User.id' => $this->Auth->user('id')),
			'fields' => array('User.theme_1','User.theme_2','User.theme_3'),
		));
		$themes = $themes['User'];

		$this->set(compact('problem','problem_id','themes'));
	}

    public function deleteMadeQuestion($question_id=null){
        $this->autoRender = false;
        if($question_id === null)
            return $this->redirect(array('action' => 'showMadeQuestions'));
		$this->loadModel('MadeProblem');
        $this->MadeProblem->delete($question_id);
        return $this->redirect(array('action' => 'showMadeQuestions'));
    }
}
