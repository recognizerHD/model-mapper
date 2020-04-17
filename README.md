# Model Mapper

[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=for-the-badge)](LICENSE.md)

## Introduction

Model Mapper is a minor change on how models are used. Many of my real world examples are contain multiple 
complex joins with equally complex where clauses. Using true eloquent objects for these scenarios are not 
beneficial. Maintenance and readability suffer, when compared to the raw equivalent SQL query that would 
be used. 

Other times where the join is simple enough, I the attribute methods are missing except for the main class
that is being instantiated. 

Finally, as I do most of my work with sqlsrv (Microsoft SQL Server), it has issues with dates.

This package is meant for my own usage, but is free for others who find it useful.

#### ~~Dates and SQLSRV (Microsoft SQL Server)~~
~~When the attribute is listed as a date attribute, it will use the internal fromDateTime method. This causes a
problem when the field is coming from sqlsrv. This overriding method strips the trailing milliseconds from the
date field so it doesn't cause errors. This will need review on subsequent updates of the laravel core incase 
they fixed this issue.~~

#### Model Mapping: AdvancedModel
Using the model for simple updates / inserts etc is great. When the queries involve multiple joins with multiple 
conditions and subqueries, the model request starts to become messy. I've created two classes to handle this problem.

The first is the AdvancedModel. This overrides the default __get method as well as adds modelMapper($array).

Usage:

```php
$reminderText = CommonData::join('Events', 'CommonData.CommonDataID', '=', 'Events.EventsCommonID')
                          ->whereIn('EventsId', $ids)
                          ->select('CommonData.reminderemailText', 'Events.EventDate', 'Events.PriceCount')
                          ->get();

$reminderText->map(function($reminder) {
    $reminder->modelMapper([
        'EventDate'         => Events::class,
        'PriceCount'        => 'integer'
    ]);
});
```
When referencing $reminderText[ $index ]→EventDate, it will now use the Events class to determine how to deal 
with the attribute instead of going to CommonData. 
 
For each field that's being returned that is part of another model, simply add it as a key→value pair in the 
modelMapper mapping call.

If the attribute requires no extra altering apart from casting it to the type that it needs to be, you can use the following simple values instead of the model class:
* integer, int
* boolean, bool
* float
* double
* string, text
 
#### Model Mapping: AdvancedResult
 
The second class is AdvancedResult. This is used for raw queries as DB::select(...) returns a collection. Each of
the attributes can be mapped to a model. If the third parameter is supplied as true, it returns a single result. 
This is the object and not a collection.

```php
$asdf = DB::select("SELECT TOP 3 * FROM Events INNER JOIN CommonData ON ...");

// The first argument is passed by reference so there's no need to set the variable to be returned.
// The second argument is optional, if you want to set it later.
AdvancedResult::make($asdf, [
    'EventDate'         => Events::class,
    'reminderemailText' => CommonData::class,
    'PriceCount'        => 'integer'
]);

// This is how you would call the argument later.
$asdf->map(function($record){
    $record->modelMapper([
        'EventDate'  => Events::class,
        'PriceCount' => 'integer'
    ]);
    return $record;
});
```