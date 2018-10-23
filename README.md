# Model Mapper



## Introduction

Model Mapper is a minor change on how models are used. Many of my real world examples are contain multiple 
complex joins with equally complex where clauses. Using true eloquent objects for these scenarios are not 
beneficial. Maintenance and readability suffer, when compared to the raw equivalent SQL query that would 
be used. 

Other times where the join is simple enough, I the attribute methods are missing except for the main class
that is being instantiated. 

Finally, as I do most of my work with sqlsrv (Microsoft SQL Server), it has issues with dates.

This package is meant for my own usage, but is free for others who find it useful.

#### Dates and SQLSRV (Microsoft SQL Server)
When the attribute is listed as a date attribute, it will use the internal fromDateTime method. This causes a 
problem when the field is coming from sqlsrv. This overriding method strips the trailing milliseconds from the 
date field so it doesn't cause errors. This will need review on subsequent updates of the laravel core incase 
they fixed this issue. 

#### Model Mapping: AdvancedModel
Using the model for simple updates / inserts etc is great. When the queries involve multiple joins with multiple 
conditions and subqueries, the model request starts to become messy. I've created two classes to handle this problem.

The first is the AdvancedModel. This overrides the default __get method as well as adds modelMapper($array).

Usage:

```php
$reminderText = WebEventsCommonData::join('WebEvents', 'WebEventsCommonData.webEventsCommonDataID', '=', 'WebEvents.WebEventsCommonID')
                                ->whereIn('WebEventsId', $ids)
                                ->select('WebEventsCommonData.reminderemailText', 'WebEvents.EventDate')
                                ->get();

$reminderText->map(function($reminder) {
    $reminder->modelMapper(['EventDate' => WebEvents::class]);
});
```
When referincing $reminderText[ $index ]→EventDate, it will now use the WebEvents class to determine how to deal 
with the attribute instead of going to WebEventsCommonData. 
 
For each field that's being returned that is part of another model, simply add it as a key→value pair in the 
modelMapper mapping call.
 
#### Model Mapping: AdvancedResult
 
The second class is AdvancedResult. This is used for raw queries as DB::select(...) returns a collection. Each of
the attributes can be mapped to a model. If the third parameter is supplied as true, it returns a single result. 
This is the object and not a collection.

```php
$asdf = DB::select("SELECT TOP 3 * FROM WebEvents");

// The first argument is passed by reference so there's no need to set the variable to be returned.
// The second argument is optional, if you want to set it later.
AdvancedResult::make($asdf, ['EventDate' => WebEvents::class]);

// This is how you would call the argument later.
$asdf->map(function($record){
    $record->modelMapper(['EventDate' => WebEvents::class]);
    return $record;
});
```