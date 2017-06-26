<?php
App::uses('AppController', 'Controller');
/**
 * PastProblems Controller
 *
 * @property PastProblem $PastProblem
 * @property PaginatorComponent $Paginator
 * @property SessionComponent $Session
 * @property FlashComponent $Flash
 */
class PastProblemsController extends AppController {

/**
 * Components
 *
 * @var array
 */
	public $components = array('Paginator', 'Session', 'Flash');

/**
 * index method
 *
 * @return void
 */
	public function index() {
		$this->PastProblem->recursive = 0;
		$this->set('pastProblems', $this->Paginator->paginate());
	}

/**
 * view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function view($id = null) {
		if (!$this->PastProblem->exists($id)) {
			throw new NotFoundException(__('Invalid past problem'));
		}
		$options = array('conditions' => array('PastProblem.' . $this->PastProblem->primaryKey => $id));
		$this->set('pastProblem', $this->PastProblem->find('first', $options));
	}

/**
 * add method
 *
 * @return void
 */
	public function add() {
		if ($this->request->is('post')) {
			$this->PastProblem->create();
			if ($this->PastProblem->save($this->request->data)) {
				$this->Flash->success(__('The past problem has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Flash->error(__('The past problem could not be saved. Please, try again.'));
			}
		}
	}

/**
 * edit method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function edit($id = null) {
		if (!$this->PastProblem->exists($id)) {
			throw new NotFoundException(__('Invalid past problem'));
		}
		if ($this->request->is(array('post', 'put'))) {
			if ($this->PastProblem->save($this->request->data)) {
				$this->Flash->success(__('The past problem has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Flash->error(__('The past problem could not be saved. Please, try again.'));
			}
		} else {
			$options = array('conditions' => array('PastProblem.' . $this->PastProblem->primaryKey => $id));
			$this->request->data = $this->PastProblem->find('first', $options);
		}
	}

/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
		$this->PastProblem->id = $id;
		if (!$this->PastProblem->exists()) {
			throw new NotFoundException(__('Invalid past problem'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->PastProblem->delete()) {
			$this->Flash->success(__('The past problem has been deleted.'));
		} else {
			$this->Flash->error(__('The past problem could not be deleted. Please, try again.'));
		}
		return $this->redirect(array('action' => 'index'));
	}
}