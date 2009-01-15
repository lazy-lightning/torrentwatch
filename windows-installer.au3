
$hostfile = "C:\Windows\system32\drivers\etc\hosts"
$ftpcmd = "C:\WINDOWS\system32\ftp.exe"
$ftpscript = "twupload.ftp"

Func Failure($failstatus = "Generic Failure")
	MsgBox(0, "Failure", $failstatus)
	Exit()
EndFunc

Func VerifyFiles()
	;; Check if our install files are here
	If Not (FileExists("tw.scripts.tar") Or FileExists("torrentwatch-installer.cgi") Or FileExists($ftpscript)) Then
		Failure("Install files not present")
	EndIf
	
	If Not (FileExists($ftpcmd)) Then
		Failure("Could not find ftp.exe")
	EndIf
	
	If Not (FileExists($hostfile)) Then
		Failure("Could not find hosts file")
	EndIf
EndFunc

Func UpdateHosts()
	If(CheckHosts()) Then
		Return
	EndIf
	
	$pch_ip = InputBox("Need IP", "Please input your NMT's IP Address")
	FileWriteLine($hostfile, $pch_ip & " popcorn localhost.drives")
	Return
EndFunc

; returns 1 if hosts has been previously setup
Func CheckHosts()
	; See if we already have a localhost.drives in hosts file
	$hosts = FileRead($hostfile)
	If @error = 1 Then
		Failure("Could not open hosts file")
	EndIf
	
	Return StringRegExp($hosts, "localhost.drives", 0)
	
	;$lines = StringSplit($hosts, "@CRLF")
	;For $i = 1 to $lines[0]
	;	;test for line
	;Next
EndFunc

Func UploadFiles()
	RunWait($ftpcmd & " -s:" & $ftpscript)
EndFunc

Func RunInstaller()
	;; Firefox
	$win1 = "Opening torrentwatch-installer.cgi"
	;; IE
	$win2 = "File Download"
	ShellExecute("http://localhost.drives:8883/HARD_DISK/torrentwatch-installer.cgi?install")
	Return

	;; Unneccesary
	$time = TimerInit()
	While Not (WinActive($win1) or WinActive($win2) or TimerDiff($time) > 2000)
		Sleep(50)
	WEnd
	WinClose($win1)
	WinClose($win2)
	
	ShellExecute("http://localhost.drives:8883/HARD_DISK/tw-iface.cgi")
EndFunc

VerifyFiles()
UpdateHosts()
UploadFiles()
RunInstaller()
