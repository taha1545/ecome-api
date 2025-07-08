<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>WebSocket Test</title>
</head>

<body>
    <h1>WebSocket Connection Test</h1>

    <script>
        const socket = new WebSocket("ws://localhost:8080/app/local-key-123?protocol=7&client=js&version=7.0.3&flash=false");


        socket.onopen = () => console.log("✅ WebSocket Connected");
        socket.onerror = (e) => console.error("❌ WebSocket Error", e);
        socket.onmessage = (e) => console.log("📩 Message", e);
    </script>
</body>

</html>