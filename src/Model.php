<?php

namespace Dfba\Exteloquent;

use Illuminate\Database\Eloquent\Model as BaseModel;
use Illuminate\Database\Eloquent\Builder as BaseModelQueryBuilder;
use Carbon\Carbon;

class Model extends BaseModel {

	/**
     * Create a new model instance that is existing.
     *
     * @param  array  $attributes
     * @param  string|null  $connection
     * @return static
     */
    public function newFromBuilder($attributes = [], $connection = null)
    {
        $model = parent::newFromBuilder($attributes, $connection);

        $model->fireModelEvent('reconstructed', false);

        /*
        retrieve
        fetch
        obtain
        reconstruct
        rebuild
        */

        return $model;
    }

    /**
     * Get the observable event names.
     *
     * @return array
     */
    public function getObservableEvents()
    {
        return array_merge(
        	$this->getObservableEvents(),
        	[
        		'reconstructed'
        	]
        );
    }

    /**
     * Register a reconstructed model event with the dispatcher.
     *
     * @param  \Closure|string  $callback
     * @param  int  $priority
     * @return void
     */
    public static function reconstructed($callback, $priority = 0)
    {
        static::registerModelEvent('reconstructed', $callback, $priority);
    }

	/**
	 * Create a new Eloquent query builder for the model.
	 *
	 * @param  \Illuminate\Database\Query\Builder  $query
	 * @return \Illuminate\Database\Eloquent\Builder|static
	 */
	public function newEloquentBuilder($query)
	{
		return new ModelQueryBuilder($query);
	}

	/**
	 * Get a new query builder instance for the connection.
	 *
	 * @return \Illuminate\Database\Query\Builder
	 */
	protected function newBaseQueryBuilder()
	{
		$conn = $this->getConnection();

		$grammar = $conn->getQueryGrammar();

		return new BaseQueryBuilder($conn, $grammar, $conn->getPostProcessor());
	}


	public function scopeGlobal($query) {

	}


	protected static function boot()
	{
		parent::boot();

		static::addGlobalScope(function(BaseModelQueryBuilder $builder) {
			$builder->getModel()->scopeGlobal($builder);
		});
	}


	protected function createDateTime($value, $format, $timezone=null)
	{
		if ($value instanceof Carbon) {
			return $value->copy();

		} else {
			return Carbon::createFromFormat($format, $value, $timezone);
		}
	}

    protected function asDateTime($value)
    {
        if ($value instanceof Carbon) {
            return $value;

        } else {
	        return $this->toDateTime($value);
	    }
    }

    /**
     * Get a fresh timestamp for the model.
     *
     * @return \App\Ntsq\Utility\Date
     */
    public function freshTimestamp()
    {
    	return $this->createDateTime(time(), 'U');
    }

    protected function toDateTime($value) {
    	// If the value is already a DateTime instance, we will just skip the rest of
    	// these checks since they will be a waste of time, and hinder performance
    	// when checking the field. We will just return the DateTime right away.
    	if ($value instanceof DateTimeInterface) {
    		return $this->createDateTime(
    			$value->format('Y-m-d H:i:s.u'),
    			'Y-m-d H:i:s.u',
    			$value->getTimeZone()
    		);
    	}

    	// If this value is an integer, we will assume it is a UNIX timestamp's value
    	// and format a Carbon object from this timestamp. This allows flexibility
    	// when defining your date fields as they might be UNIX timestamps here.
    	if (is_numeric($value)) {
    		return $this->createDateTime($value, 'U');
    	}

    	// If the value is in simply year, month, day format, we will instantiate the
    	// Carbon instances from that format. Again, this provides for simple date
    	// fields on the database, while still supporting Carbonized conversion.
    	if (preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})$/', $value)) {
    		return $this->createDateTime($value .' 00:00:00', 'Y-m-d H:i:s');
    	}

    	// Finally, we will just assume this date is in the format used by default on
    	// the database connection and use that format to create the Carbon object
    	// that is returned back out to the developers after we convert it here.
    	return $this->createDateTime($value, $this->getDateFormat());
    }

}