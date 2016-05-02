@echo off &setlocal
cls

ECHO STATUS:
ECHO.
git status

ECHO.
SET "next=y"
SET /p next="CONTINUE?[Y/n]: "
IF %next%==n (
	ECHO EXITING.
	exit 0;
)

ECHO.
SET "pull=y"
SET /p pull="PULL BEFORE COMMIT?[Y/n]: "
IF %pull%==y (
	git pull
)

ECHO.
SET "add=y"
SET /p add="ADD ALL BEFORE COMMIT?[Y/n]: "
IF %add%==y (
	git add --all
)

ECHO.
SET "com=y"
SET /p com="COMMIT USING -am?[Y/n]: "
ECHO.
SET msg="Console commit"
SET /p msg="MESSAGE FOR COMMIT?[Console commit]: "
IF %com%==y (
	SET comm_cmd=git commit -am %msg%
) else (
	SET comm_cmd=git commit -m %msg%
)
%comm_cmd%

ECHO.
SET "push=y"
SET /p push="PUSH?[Y/n]: "
IF %push%==y (
	git push
)

ECHO.
ECHO STATUS:
git status
ECHO.
