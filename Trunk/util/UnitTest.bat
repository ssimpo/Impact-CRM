@echo off
if defined "%2" goto :special else goto :all

:special
if "%2" == "all" goto :all
if "%2" == "" goto :all
echo Running Unit Tests contained in: unit_tests\%2...
call phpunit unit_tests\%2 > unittest.log
goto :end

:all
echo Running Unit Tests on Impact Project...
call phpunit unit_tests\ > unittest.log
goto :end

:end
echo DONE
%1 unittest.log