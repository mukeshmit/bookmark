<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Bookmarks Model
 *
 * @property \App\Model\Table\UsersTable|\Cake\ORM\Association\BelongsTo $Users
 * @property \App\Model\Table\TagsTable|\Cake\ORM\Association\BelongsToMany $Tags
 *
 * @method \App\Model\Entity\Bookmark get($primaryKey, $options = [])
 * @method \App\Model\Entity\Bookmark newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\Bookmark[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Bookmark|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Bookmark patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Bookmark[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\Bookmark findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class BookmarksTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);
		

        $this->setTable('bookmarks');
        $this->setDisplayField('title');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
		
		// Add the behaviour to your table
        $this->addBehavior('Search.Search');

		// Setup search filter using search manager
        $this->searchManager()
            ->value('user_id')
            // Here we will alias the 'q' query param to search the `Bookmarks.title`
            // field and the `Bookmarks.description` field, using a LIKE match, with `%`
            // both before and after.
            ->add('q', 'Search.Like', [
                'before' => true,
                'after' => true,
                'fieldMode' => 'OR',
                'comparison' => 'LIKE',
                'wildcardAny' => '*',
                'wildcardOne' => '?',
                'field' => ['title', 'description']
            ]);
		

        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsToMany('Tags', [
            'foreignKey' => 'bookmark_id',
            'targetForeignKey' => 'tag_id',
            'joinTable' => 'bookmarks_tags'
        ]);
		
		
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator)
    {
        $validator
            ->integer('id')
            ->allowEmpty('id', 'create');

        $validator
            ->scalar('title')
            ->allowEmpty('title');

        $validator
            ->scalar('description')
            ->allowEmpty('description');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules)
    {
        $rules->add($rules->existsIn(['user_id'], 'Users'));

        return $rules;
    }
	
	// The $query argument is a query builder instance.
	// The $options array will contain the 'tags' option we passed
	// to find('tagged') in our controller action.
	public function findTagged(Query $query, array $options)
	{
		$bookmarks = $this->find()
			->select(['id', 'url', 'title', 'description']);

		if (empty($options['tags'])) {
			$bookmarks
				->leftJoinWith('Tags')
				->where(['Tags.title IS' => null]);
		} else {
			$bookmarks
				->innerJoinWith('Tags')
				->where(['Tags.title IN ' => $options['tags']]);
		}

		return $bookmarks->group(['Bookmarks.id']);
	}
	
	public function beforeSave($event, $entity, $options)
	{
		
		if ($entity->tag_string) {
		
			$entity->tags = $this->_buildTags($entity->tag_string);
			/* 	pr($entity);
			die; */
		}
	}

	protected function _buildTags($tagString)
	{
		// Trim tags
		$newTags = array_map('trim', explode(',', $tagString));
		// Remove all empty tags
		$newTags = array_filter($newTags);
		// Reduce duplicated tags
		$newTags = array_unique($newTags);

		$out = [];
		$query = $this->Tags->find()
			->where(['Tags.title IN' => $newTags]);

		// Remove existing tags from the list of new tags.
		foreach ($query->extract('title') as $existing) {
			$index = array_search($existing, $newTags);
			if ($index !== false) {
				unset($newTags[$index]);
			}
		}
		// Add existing tags.
		foreach ($query as $tag) {
			$out[] = $tag;
		}
		// Add new tags.
		foreach ($newTags as $tag) {
			$out[] = $this->Tags->newEntity(['title' => $tag,'user_id' => $_SESSION['Auth']['User']['id']]);
		}
		return $out;
		
	}
	
}
