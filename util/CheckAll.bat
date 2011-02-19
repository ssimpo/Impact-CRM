@echo off
call UnitTest.bat %1 all
call CodeCheck.bat %1 all
echo DONE ALL