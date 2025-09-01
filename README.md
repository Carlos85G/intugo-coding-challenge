# Intugo coding challenge

by Carlos González (carlos85g at gmail dot com)

## Dynamic Rule Engine (Policy-like System)

Class `App\Helpers\RuleEvaluator` takes a group of rules (a ruleset) and uses it to compare the data stored for a particular user. The helper is provisioned of user by the HTTP request, provisioned of rulesets through middleware `App\Http\Middleware\EnsureActionAllowed`, and the action is provided by the route or group of routes as middleware parameters, like so:

```
Route::middleware([
    ...,
    EnsureActionAllowed::class.':submit_form',
    ...
])->group(function() {
    /* Routes the middleware */
});
```

In the preceding example, `submit_form` represents the action of a ruleset in the database. The ruleset for that action will be retrieved and the user data will be evaluated against it. If the user is authorized, they will be allowed to consume the resource; otherwise, access will be forbidden.

### Considerations:
- Created new model `App\Models\Ruleset` to store JSON rulesets. New dynamic columns are parsed from the data, to help with indexation.
- Property `action` has been considered as a unique key between rulesets. Database constraints won't allow for two rulesets with the same action.
- A new property, `role`, has been added to the stock `App\Models\User` model, to accomodate for the ruleset example.
- Laravel Passport was used to achieve user authorization through token.
- For operators `in` and `not_tn` rule value must be array, to make a proper haystack.
- User needs to be populated manually, along with the token retrieval.

## Nested Eloquent Search Filter

The static method `makeBuilderFromJson` in class `App\Helpers\ModelJsonFilter` uses a JSON string to filter a referenced model query builder in accordance to the specified rules.

```
/** @var Builder */
$query = App\Models\Appointment::query();

App\Helpers\ModelJsonFilter::makeBuilderFromJson(
    '{"patient.name": "John", "appointment.status": "confirmed", "location.city": "Dallas"}',
    $query
);

$query->toSql();
/*
    "select * from `appointments` where exists (select * from `patients` where `appointments`.`patient_id` = `patients`.`id` and `name` = ?) and `status` = ? and exists (select * from `locations` where `appointments`.`location_id` = `locations`.`id` and `city` = ?)"
*/
$query->getBindings();
/*
    [
        "John",
        "confirmed",
        "Dallas",
    ]
*/
```

After the process, the results of `$query->get()` will have been filtered.

### Considerations:
- Created models and seeders for Appointment, Location and Patient.
- Because of the nature of JSON objects, all top-level relationship filters are evaluated as `where` or `whereHas`; `orWhere` and `orWhereHas` for rules with multiple values, with the intent of nesting.
- If the relationship filter begins with the name of the current class, it will be flattened to the next level; for example, in an Accomodation builder, the filter `'accomodation.location.city'` is processed as `'location.city'`.
- Due to technical difficulties, a pseudo-test is done in endpoint `/api/check-sql`


## State Machine for Models

Trait `App\Traits\HasTransitionalStates` is a reusable bit of code that allows a model to traverse states from a static tree. After use, the model's `transitionTo()` method wll evaluate if the transition is possible and will make the changes to the object and database, if allowed. For convenience, the method returns a boolean value that tells if the transition was allowed or not.

If a transition is allowed, the trait's code will dispatch events `App\Events\ModelTransitioning` at the moment the transition process starts, and `App\Events\ModelTransitioned` at the moment the transition ends. Event `App\Events\ModelTransitioning` contains the model, the current state and the new state, while event `App\Events\ModelTransitioned` contains the model and the current state. The events dispatched by the saving process are listened by `App\Listeners\ModelTransitioning` and `App\Listeners\ModelTransitioned`, respectively.

```
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasTransitionalStates;

class Document extends Model
{
    use HasTransitionalStates;
}
```

### Considerations:
- Created models and seeders for Document.
- Currently, the events add an INFO-level log entry.