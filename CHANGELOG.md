# Changelog

All notable changes will be documented in this file.
## 2020-06-25: v.1.2.3
#### Changed
- isset doesn't return parent attribute.\
  In the case of a attribute request like:\
  $this->attribute ?? [ ];\
  It would return a null even if a parent object contains that attribute. The fix would do one last check on the parent before returning null.

## 2020-05-21: v.1.2.2
#### Changed
- PSR-4 Typo

## 2020-05-19: v.1.2.1
#### Changed
- Vendor Update

## 2020-04-17: v.1.2.0
#### Added
- Allow for basic type casting instead of using the model's get*Attribute method. If you want it to be an integer but don't care about the rest of the attribute methods, you can use "integer". Check the readme for more types.

## 2019-08-12: v.1.1.8
#### Changed
-  is_callable fetches the method as an attribute

## 2019-07-02: v.1.1.7
#### Changed
- A bug where a set Attribute mutator was run multiple times when the value was from a foreign model. Foreign values set raw values instead of attribute mutators. That way the mutator is only run once.

## 2019-03-25: v.1.1.6
#### Added
- It uses the default connection. I think it very unlikely that raw result will be used to combine records from two different database drivers. In the case when two different drivers are needed, I'll add that feature then.

## 2018-12-11: v.1.1.5
#### Added
- The sqlsrv date format issue in Laravel seems to be fixed. We don't need the getDateFormat override anymore.
- Get the first foreign model's connection loaded into the advanced result

## 2018-11-21: v.1.1.4
#### Changed
- Check that the attribute exists before referencing it.

## 2018-11-13: v.1.1.3
#### Added
- Allow for inter model mapping

## 2018-11-09: v.1.1.2
#### Changed
- Empty array was returning an array, not a collection

## 2018-10-22: v.1.1.1
#### Added
- Allow for raw result to return json and string

## 2018-10-24: v.1.1.0
#### Changed
- Proper namespace definition

## 2018-10-22: v.1.0.7
#### Changed
- Migrated to a new project.

## 2018-08-24: v.1.0.6
#### Added
- Support setting attributes.

## 2018-06-28: v.1.0.5
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