#!/bin/sh
clear

lines=`tput lines`
LINES=`echo "${lines}-14"|bc`
cols=`tput cols`
COLS=`echo "${cols}-14"|bc`
STATUS=`git status`

DIALOG=whiptail
if [ -x /usr/bin/dialog ] ;then DIALOG=gdialog ; fi
if [ -x /usr/bin/xdialog ] ;then DIALOG=xdialog ; fi

if ($DIALOG --clear --title "CONTINUE ?" --yesno "${STATUS}" $LINES $COLS); then
	COMMIT=$($DIALOG --title "COMMIT FLAG" --radiolist "Commit using -am flag ?" 9 `echo "$COLS-3"|bc` 2 "yes" "YES. auto-add modified/deleted files to commit" ON "no" "NO. Add/remove files separately" OFF 3>&1 1>&2 2>&3)
	exitstatus=$?
	if [ $exitstatus = 0 ]; then
		MSG=$($DIALOG --title "COMMIT MESSAGE" --inputbox "Enter a message for current commit" 8 $COLS "Console commit" 3>&1 1>&2 2>&3)
		exitstatus=$?
		if [ $exitstatus = 0 ]; then
			QUEUE=$($DIALOG --title "QUEUE" --separate-output --checklist "Select queue items" 11 $COLS 4 "pull" "PULL BEFORE COMMIT (git pull)" ON "add" "ADD ALL FILESTO COMMIT (git add --all)" ON "commit" "COMMIT (git commit -m(a) ${MSG})" ON "push" "PUSH AFTER COMMIT (git push (-u orugin master) )" ON 3>&1 1>&2 2>&3)
			exitstatus=$?
			printf "%s\n" $QUEUE | while IFS= read -r choice ;
			do
				case $choice in
					pull)
						echo "PULL BEFORE COMMIT"; echo;
						git pull; echo
					;;
					add)
						echo "ADD ALL BEFORE COMMIT"; echo;
						git add --all; echo
					;;
					commit)
						if [ ${COMMIT} = "yes" ]; then
							echo "COMMIT using -am with message ${MSG}"; echo
							git commit -am "${MSG}"
						else
							echo "COMMIT using -m with message ${MSG}"; echo
							git commit -m "${MSG}"
						fi
						echo
					;;
					push)
						PUSH=$($DIALOG --title "PUSH TO..." --radiolist "Push to:" 9 `echo "$COLS-3"|bc` 2 \
						"om" "Directly to Origin->Master" ON \
						"def" "Default push without flags" OFF 3>&1 1>&2 2>&3)
						exitstatus=$?
						echo "PUSH"
						echo
						if [ $exitstatus = 0 ]; then
							if [ ${PUSH}="om" ]; then
								git push -u origin master
							else
								git push
							fi
						else
							exit 0;
						fi
						echo
					;;
					*)
					;;
				esac
			done;
			clear
			git status
		else
			exit 0;
		fi
	else
		exit 0;
	fi
else
	exit 0;
fi
