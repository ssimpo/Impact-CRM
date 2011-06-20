@echo off
if defined "%2" goto :special else goto :all

:special
if "%2" == "all" goto :all
if "%2" == "models" goto :models
if "%2" == "plugins" goto :plugins
if "%2" == "controllers" goto :controllers
if "%2" == "analytics" goto :analytics
if "%2" == "" goto :all
echo Running Unit Tests contained in: %2...
call phpunit %2 > unittest.log
goto :end

:all
echo Running Unit Tests on Impact Project...
call phpunit unit_tests\ > unittest.log
goto :end

:models
echo Running Unit Tests on Impact Models...
call phpunit unit_tests\models\ > unittest.log
goto :end

:plugins
echo Running Unit Tests on Impact Models...
call phpunit unit_tests\plugins\ > unittest.log
goto :end

:controllers
echo Running Unit Tests on Impact Models...
call phpunit unit_tests\controllers\ > unittest.log
goto :end

:analytics
echo Running Unit Tests on Analytics Module...
call phpunit ..\analytics\util\unit_tests\ > unittest.log
goto :end

:end
echo DONE
%1 unittest.log