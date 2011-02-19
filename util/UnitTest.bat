@echo off
echo Running Unit Tests on Impact Project...

if defined "%2" goto :special else goto :all

:special
if "%2" == "all" goto :all
if "%2" == "" goto :all
call phpunit unit_tests\%2 > unittest.log
goto :end

:all
call phpunit unit_tests\ > unittest.log
goto :end

:end
echo DONE
%1 unittest.log