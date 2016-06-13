<?php

namespace Dfba\Exteloquent;

use Illuminate\Database\Query\Builder as BaseBuilder;

class BaseQueryBuilder extends BaseBuilder {

	public function newJoinClause($type, $table) {
	    return new JoinClause($type, $table);
	}

	/**
	 * Add a join clause to the query.
	 *
	 * @param  string  $table
	 * @param  string  $one
	 * @param  string  $operator
	 * @param  string  $two
	 * @param  string  $type
	 * @param  bool    $where
	 * @return $this
	 */
	public function join($table, $one, $operator = null, $two = null, $type = 'inner', $where = false)
	{
	    // If the first "column" of the join is really a Closure instance the developer
	    // is trying to build a join with a complex "on" clause containing more than
	    // one condition, so we'll add the join and call a Closure with the query.
	    if ($one instanceof \Closure) {
	        $join = $this->newJoinClause($type, $table);

	        call_user_func($one, $join);

	        $this->joins[] = $join;

	        $this->addBinding($join->bindings, 'join');
	    }

	    // If the column is simply a string, we can assume the join simply has a basic
	    // "on" clause with a single condition. So we will just build the join with
	    // this simple join clauses attached to it. There is not a join callback.
	    else {
	        $join = $this->newJoinClause($type, $table);

	        $this->joins[] = $join->on(
	            $one, $operator, $two, 'and', $where
	        );

	        $this->addBinding($join->bindings, 'join');
	    }

	    return $this;
	}

}