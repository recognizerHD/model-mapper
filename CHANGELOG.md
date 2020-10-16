# Changelog

## 2020-10-16: v1.3.7
#### Changed
- UTC Dates were being set as local time when utcAsLocal = true. It should only convert set the timezone to local when using getAttribute, not setAttribute.

## 2020-09-02: v1.3.6
#### Changed
- Updated vendor files

## 2020-08-17: v1.3.5
#### Changed
- Setting the connection object on anything but AdvancedResult throws an error.

All notable changes will be documented in this file.
## 2020-08-13: v1.3.4
#### Changed
- Fixed an issue with raw attributes being set with transformed values.

## 2020-07-08: v1.3.3
#### Changed
- Fixed bug with timezone when format not defined.
- Removed redundant timezone set.

## 2020-07-06: v1.3.2
#### Changed
- Found another bug with array_merge

## 2020-07-06: v1.3.1
#### Changed
- Fixed issue with in_array error

## 2020-06-30: v1.3.0
#### Added
- UTC Dates are now supported.\
  $this->utcDates = [];\
  $this->utcAsLocal will convert all utc dates to local time but will be imported as UTC unless supplied as a DateTime object with timezone information already.
#### Changed
- Version numbers incorrect in change log.

## 2020-06-25: v1.2.3
#### Changed
- isset doesn't return parent attribute.\
  In the case of a attribute request like:\
  $this->attribute ?? [ ];\
  It would return a null even if a parent object contains that attribute. The fix would do one last check on the parent before returning null.

## 2020-05-21: v1.2.2
#### Changed
- PSR-4 Typo

## 2020-05-19: v1.2.1
#### Changed
- Vendor Update

## 2020-04-17: v1.2.0
#### Added
- Allow for basic type casting instead of using the model's get*Attribute method. If you want it to be an integer but don't care about the rest of the attribute methods, you can use "integer". Check the readme for more types.

## 2019-08-12: v1.1.8
#### Changed
-  is_callable fetches the method as an attribute

## 2019-07-02: v1.1.7
#### Changed
- A bug where a set Attribute mutator was run multiple times when the value was from a foreign model. Foreign values set raw values instead of attribute mutators. That way the mutator is only run once.

## 2019-03-25: v1.1.6
#### Added
- It uses the default connection. I think it very unlikely that raw result will be used to combine records from two different database drivers. In the case when two different drivers are needed, I'll add that feature then.

## 2018-12-11: v1.1.5
#### Added
- The sqlsrv date format issue in Laravel seems to be fixed. We don't need the getDateFormat override anymore.
- Get the first foreign model's connection loaded into the advanced result

## 2018-11-21: v1.1.4
#### Changed
- Check that the attribute exists before referencing it.

## 2018-11-13: v1.1.3
#### Added
- Allow for inter model mapping

## 2018-11-09: v1.1.2
#### Changed
- Empty array was returning an array, not a collection

## 2018-10-22: v1.1.1
#### Added
- Allow for raw result to return json and string

## 2018-10-24: v1.1.0
#### Changed
- Proper namespace definition

## 2018-10-22: v1.0.7
#### Changed
- Migrated to a new project.

## 2018-08-24: v1.0.6
#### Added
- Support setting attributes.

## 2018-06-28: v1.0.5
#### Added
- Allow for object to be accessed as an array.

## 2018-06-28: v1.0.4
#### Added
- Visible attribute and original attributes.

## 2018-06-27: v1.0.2, v1.0.3
#### Added
- Allow for AdvancedResult to handle single record mapping returning only the object instead of a collection.
#### Fixed
- Typo from verson 1.0.2.

## 2018-06-06
Initial commit 