<?php

namespace Blok\Commentable\Models;

use Blok\Commentable\Traits\HasComments;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Kalnoy\Nestedset\NodeTrait;

class Comment extends Model
{
    use NodeTrait;
    use HasComments;

    /**
     * @var array
     */
    protected $guarded = ['id', 'created_at', 'updated_at'];

    /**
     * @return mixed
     */
    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @param Model $commentable
     * @param $data
     * @param Model $creator
     *
     * @return static
     */
    public static function createComment(Model $commentable, $data, Model $creator): self
    {
        return $commentable->comments()->create(array_merge($data, [
            'creator_id' => $creator->getAuthIdentifier(),
            'creator_type' => get_class($creator),
        ]));
    }

    /**
     * @return mixed
     */
    public function creator(): MorphTo
    {
        return $this->morphTo('creator');
    }

    /**
     * @param $id
     *
     * @return mixed
     * @throws \Exception
     */
    public function deleteComment($id): bool
    {
        return (bool)static::find($id)->delete();
    }

    /**
     * Fetch all mentioned users within the reply's body.
     *
     * @return array
     */
    public function mentionedUsers()
    {
        preg_match_all('/@([\w\-]+)/', $this->body, $matches);
        return $matches[1];
    }

    /**
     * @return array|void
     */
    public function toArray()
    {
        $data = parent::toArray();

        $data['comments'] = [];

        if ($this->hasChildren()) {
            $data['comments'] = $this->children->toArray();
        }

        $data['creator'] = $this->creator;

        return $data;
    }

    /**
     * @return bool
     */
    public function hasChildren(): bool
    {
        return $this->children()->count() > 0;
    }

    /**
     * @param $id
     * @param $data
     *
     * @return mixed
     */
    public function updateComment($id, $data): bool
    {
        return (bool)static::find($id)->update($data);
    }
}
