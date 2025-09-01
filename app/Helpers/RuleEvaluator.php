<?php

namespace App\Helpers;

use App\Models\User;
use App\Models\Ruleset;
use App\Exceptions\RuleEvaluator\OperationInvalidException;
use App\Exceptions\RuleEvaluator\UserNotFoundException;
use Illuminate\Support\Facades\Auth;

/**
 * Helper class to evaluate user authorization to a resource tag
 * 
 * @author Carlos González
 */
class RuleEvaluator {
    /**
     * Valid operation function suffixes,
     * keyed by expected operator.
     * 
     * New operations must be added as PascalCase
     * and expected to work with the "is" suffix.
     * 
     * @var string[]
     */
    protected array $allowedOperators = [
        "==" => "Equal",
        "!=" => "NotEqual",
        "in" => "In",
        "not_in" => "NotIn",
        ">" => "GreaterThan",
        "<" => "LesserThan",
        "contains" => "Contains"
    ];

    /**
     * Public function to check if a user is authorized to perform an action
     * 
     * @param   string      $action The action identifier
     * @param   User|null   $user   Optional: The user. Will default to
     *                              currently-authenticated user.
     * @return  bool                The evaluation.
     * @throws  Exception
     */
    public function isUserAllowed(string $action, ?User $user = null): bool
    {
        /* If not provided in params, provision the user via Auth */
        $this->retrieveUser($user);

        return $this->doesUserPassRules($action, $user);
    }

    /**
     * Protected function to populate user, if null
     * 
     * @param   User|null The user.
     * @return  void
     * @throws  UserNotFoundException
     */
    protected function retrieveUser(?User &$user): void
    {
        if (is_null($user)) {
            $user = Auth::user();

            if (is_null($user)) {
                throw new UserNotFoundException("User is not provided nor authenticated");
            }
        }
    }

    /**
     * Protected function to check user data against a set of rules
     * 
     * @param   string    $action The action identifier
     * @param   User      $user   The user.
     * @return  bool              The evaluation.
     * @throws  Exception
     */
    protected function doesUserPassRules(string $action, User $user): bool
    {
        $result = true;

        /** @var mixed[] Rules that apply for action */
        $rules = $this->retrieveRulesetForAction(
            $user,
            $action
        );

        if (empty($rules)) {
            /* No rules found for action. User assumed unauthorized */
            $result = false;
        }

        foreach ($rules as $rule) {
            /** @var string */
            $operator = $rule["operator"];
            
            if (!array_key_exists($operator, $this->allowedOperators)) {
                throw new OperationInvalidException("Operator invalid '{$operator}' in rule for ruleset with action '{$action}'");
            }

            if (!$result = $this->isUserValidByRule($user, $rule)) {
                break;
            }
        }

        return $result;
    }

    /**
     * Protected function that retrieves a set of rules from the user,
     * determined by an action identifier.
     * 
     * @param   User    $user   The user.
     * @param   string  $action The action identifier.
     * @return  mixed[]         Set of rules to test against. 
     */
    protected function retrieveRulesetForAction(User $user, string $action) : array
    {
        $ruleset = optional(
            Ruleset::where('action', $action)
                    ->first(),
            fn (Ruleset $ruleset) => $ruleset->rules->all()
        );

        if (is_null($ruleset)) {
            $ruleset = [];
        }

        return $ruleset;
    }

    /**
     * Protected function to test user data against a rule.
     * 
     * @param   User $user      The user.
     * @param   mixed[] $rule   The user rule to check against.
     * @return  bool            The evaluation.
     */
    protected function isUserValidByRule (User $user, array $rule) : bool
    {
        /**
         * @var string        $field       The name of the field the rule operates on
         * @var string        $operator    The operator the rule imposes
         * @var mixed|mixed[] $value       The value or values the rule requires to check against
         */
        extract($rule);
        
        /* Use proper operation */
        return $this->{"is{$this->allowedOperators[$operator]}"}(
            $user->{$field},
            $value
        );
    }

    /**
     * Protected function to define a comparison of values for equality
     * 
     * @param   mixed   $a  The first parameter.
     * @param   mixed   $a  The second parameter.
     * @return  bool        The evaluation.
     */
    protected function isEqual(mixed $a, mixed $b): bool
    {
        return ($a == $b);
    }

    /**
     * Protected function to define a comparison of values for inequality
     * 
     * @param   mixed   $a  The first parameter.
     * @param   mixed   $b  The second parameter.
     * @return  bool        The evaluation.
     */
    protected function isNotEqual(mixed $a, mixed $b): bool
    {
        return !$this->isEqual($a, $b);
    }

    /**
     * Protected function to define a comparison of values, where
     * the first parameter must be greater than the second.
     * 
     * @param   mixed   $a  The first parameter.
     * @param   mixed   $b  The second parameter.
     * @return  bool        The evaluation.
     */
    protected function isGreaterThan(mixed $a, mixed $b): bool
    {
        return ($a > $b);
    }

    /**
     * Protected function to define a comparison of values, where
     * the first parameter must be lesser than the second.
     * 
     * @param   mixed   $a  The first parameter.
     * @param   mixed   $b  The second parameter.
     * @return  bool        The evaluation.
     */
    protected function isLesserThan(mixed $a, mixed $b): bool
    {
        return ($a < $b);
    }

    /**
     * Protected function to define a search of value, where
     * the needle must be contained in the haystack.
     * 
     * @param   mixed   $needle     The parameter to look for.
     * @param   mixed[] $haystack   The parameter to search in.
     * @return  bool                The evaluation.
     */
    protected function isIn(mixed $needle, array $haystack): bool
    {
        return collect($haystack)
                ->contains($needle);
        ;
    }

    /**
     * Protected function to define a search of value, where
     * the needle must not be contained in the haystack.
     * 
     * @param   mixed   $needle     The parameter to look for.
     * @param   mixed[] $haystack   The parameter to search in.
     * @return  bool                The evaluation.
     */
    protected function isNotIn(mixed $needle, array $haystack): bool
    {
        return !$this->isIn($needle, $haystack);
    }

    /**
     * Protected function to define a search of value, where
     * the substring must be part of the fullstring.
     * 
     * @param   string   $substring     The parameter to look for.
     * @param   string   $fullstring    The parameter to search in.
     * @return  bool                    The evaluation.
     */
    protected function isContains(string $substring, string $fullstring): bool
    {
        return str_contains($substring, $fullstring);
    }
}