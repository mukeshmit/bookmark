<?php
namespace App\Controller;

use App\Controller\AppController;

/**
 * Bookmarks Controller
 *
 * @property \App\Model\Table\BookmarksTable $Bookmarks
 *
 * @method \App\Model\Entity\Bookmark[] paginate($object = null, array $settings = [])
 */
class BookmarksController extends AppController
{
	/* public $paginate = [
					'limit' => 5,
					'order' => [
						'Bookmarks.title' => 'asc'
					]
				]; */
				
	public function initialize()
	{
		parent::initialize();

		$this->loadComponent('Search.Prg', [
			// This is default config. You can modify "actions" as needed to make
			// the PRG component work only for specified methods.
			'actions' => ['index']
		]);
		 
		
	}

    /**
     * Index method
     *
     * @return \Cake\Http\Response|void
     */
    public function index()
    {
		$query = $this->Bookmarks
				// Use the plugins 'search' custom finder and pass in the
				// processed query params
				->find('search', ['search' => $this->request->query])
				// You can add extra things to the query if you need to
				->contain(['Users'])
				->where(['title IS NOT' => null])
				->order(['Bookmarks.id' => 'DESC'])
				->limit(7);
        // ->where(['Bookmarks.user_id' => $this->Auth->user('id')]);
		
		// pr($query);
		// die;
		$this->set('bookmarks', $this->paginate($query));
       /*  $this->paginate = [
			'conditions' => [
				'Bookmarks.user_id' => $this->Auth->user('id'),
			]
		];
		$this->set('bookmarks', $this->paginate($this->Bookmarks));
		$this->set('_serialize', ['bookmarks']); */
		
    }

    /**
     * View method
     *
     * @param string|null $id Bookmark id.
     * @return \Cake\Http\Response|void
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $bookmark = $this->Bookmarks->get($id, [
            'contain' => ['Users', 'Tags']
        ]);

        $this->set('bookmark', $bookmark);
        $this->set('_serialize', ['bookmark']);
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $bookmark = $this->Bookmarks->newEntity();
		
        if ($this->request->is('post')) {
			
            $bookmark = $this->Bookmarks->patchEntity($bookmark, $this->request->getData());
			
			$bookmark->user_id = $this->Auth->user('id');
			/* pr($this->Bookmarks);
			pr($bookmark);
			die; */
            if ($this->Bookmarks->save($bookmark)) {
                $this->Flash->success(__('The bookmark has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The bookmark could not be saved. Please, try again.'));
        }
        // $users = $this->Bookmarks->Users->find('list', ['limit' => 200]);
        $tags = $this->Bookmarks->Tags->find('list');
        $this->set(compact('bookmark','tags'));
        // $this->set(compact('bookmark', 'users', 'tags'));
        // $this->set('_serialize', ['bookmark']);
    }

    /**
     * Edit method
     *
     * @param string|null $id Bookmark id.
     * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $bookmark = $this->Bookmarks->get($id, [
            'contain' => ['Tags']
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $bookmark = $this->Bookmarks->patchEntity($bookmark, $this->request->getData());
			$bookmark->user_id = $this->Auth->user('id');
			// pr($bookmark);
			// die;
            if ($this->Bookmarks->save($bookmark)) {
                $this->Flash->success(__('The bookmark has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The bookmark could not be saved. Please, try again.'));
        }
        $tags = $this->Bookmarks->Tags->find('list');
		$this->set(compact('bookmark', 'tags'));
		$this->set('_serialize', ['bookmark']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Bookmark id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $bookmark = $this->Bookmarks->get($id);
        if ($this->Bookmarks->delete($bookmark)) {
            $this->Flash->success(__('The bookmark has been deleted.'));
        } else {
            $this->Flash->error(__('The bookmark could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
	
	public function tags()
	{
		// The 'pass' key is provided by CakePHP and contains all
		// the passed URL path segments in the request.
		$tags = $this->request->getParam('pass');

		// Use the BookmarksTable to find tagged bookmarks.
		$bookmarks = $this->Bookmarks->find('tagged', [
			'tags' => $tags
		]);

		// Pass variables into the view template context.
		$this->set([
			'bookmarks' => $bookmarks,
			'tags' => $tags
		]);
	}
	
	public function custompage(){
		
		
		
	}
}
