@echo off
echo Running Code Checks on Impact Project...

if defined "%2" goto :special else goto :all

:special
if "%2" == "all" goto :all
if "%2" == "" goto :all
if "%2" == "impact" goto :impact
if "%2" == "pear" goto :pear
goto :all

:impact
call phpcs --standard=Impact ../models/ ../controllers/ ../plugins/ ../index.php unit_tests/models/ unit_tests/plugins/ unit_tests/controllers/ > codecheck_impact.log
%1 codecheck_impact.log
goto :end

:pear
call phpcs --standard=PEAR PEAR/ > codecheck_pear.log
%1 codecheck_pear.log
goto :end

:all
call phpcs --standard=Impact ../models/ ../controllers/ ../plugins/ ../index.php unit_tests/models/ unit_tests/plugins/ unit_tests/controllers/ > codecheck_impact.log
call phpcs --standard=PEAR PEAR/ > codecheck_pear.log
%1 codecheck_impact.log
%1 codecheck_pear.log
goto :end

:end
echo DONE
