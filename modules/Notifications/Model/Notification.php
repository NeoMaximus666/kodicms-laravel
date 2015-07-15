<?php namespace KodiCMS\Notifications\Model;

use Carbon\Carbon;
use KodiCMS\Users\Model\User;
use Illuminate\Database\Eloquent\Model;
use KodiCMS\Notifications\Types\DefaultNotificationType;
use KodiCMS\Notifications\Contracts\NotificationTypeInterface;
use KodiCMS\Notifications\Contracts\NotificationObjectInterface;

class Notification extends Model {

	/**
	 * @var Model
	 */
	private $relatedObject = null;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['sender_id', 'type', 'message', 'object_id', 'object_type', 'sent_at', 'parameters'];

	/**
	 * The attributes that should be mutated to dates.
	 *
	 * @var array
	 */
	protected $dates = ['sent_at'];

	/**
	 * The attributes that should be casted to native types.
	 *
	 * @var array
	 */
	protected $casts = [
		'sender_id' => 'integer',
		'object_id' => 'integer',
		'type' => 'string',
		'message' => 'string',
		'object_type' => 'string',
		'parameters' => 'array',
	];

	/*******************************************************************************************
	 * Mutators
	 *******************************************************************************************/
	/**
	 * @param string $type
	 * @return NotificationTypeInterface
	 */
	public function getTypeAttribute($type)
	{
		$type = class_exists($type) ? new $type : new DefaultNotificationType;
		$type->setObject($this);

		return $type;
	}

	/**
	 * @param string $message
	 *
	 * @return $this
	 */
	public function withMessage($message)
	{
		$this->message = $message;
		return $this;
	}

	/**
	 * @param array $parameters
	 *
	 * @return $this
	 */
	public function withParameters(array $parameters)
	{
		$this->parameters = $parameters;
		return $this;
	}

	/**
	 * @param NotificationTypeInterface $type
	 *larav
	 * @return $this
	 */
	public function withType(NotificationTypeInterface $type)
	{
		$this->type = get_class($type);
		return $this;
	}

	/**
	 * @param NotificationObjectInterface $object
	 *
	 * @return $this
	 */
	public function regarding(NotificationObjectInterface $object)
	{
		$this->object_id = $object->getId();
		$this->object_type = get_class($object);

		return $this;
	}

	/**
	 * @param User $user
	 *
	 * @return $this
	 */
	public function from(User $user)
	{
		$this->sender()->associate($user);
		return $this;
	}

	/**
	 * @param array $users
	 *
	 * @return $this
	 */
	public function deliver(array $users = [])
	{
		$this->sent_at = new Carbon;
		$this->save();

		foreach ($users as $user)
		{
			$this->users()->attach($user);
		}

		return $this;
	}

	/**
	 * @return bool
	 */
	public function hasValidObject()
	{
		try
		{
			$className = $this->object_type;
			$object = new $className($this->object_id);
		}
		catch (\Exception $e)
		{
			return false;
		}

		$this->relatedObject = $object;

		return true;
	}

	/**
	 * @return Model
	 * @throws \Exception
	 */
	public function getObject()
	{
		if (is_null($this->relatedObject))
		{
			$hasObject = $this->hasValidObject();

			if (!$hasObject)
			{
				throw new \Exception(sprintf("No valid object (%s with ID %s) associated with this notification.", $this->object_type, $this->object_id));
			}
		}

		return $this->relatedObject;
	}

	/*******************************************************************************************
	 * Relations
	 *******************************************************************************************/
	/**
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
	 */
	public function users()
	{
		return $this->belongsToMany(User::class, 'notifications_users', 'notification_id');
	}

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function sender()
	{
		return $this->belongsTo(User::class, 'sender_id');
	}
}