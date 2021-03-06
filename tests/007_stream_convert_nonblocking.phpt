--TEST--
Test for skyray\stream\Stream() connvert to non-blocking
--SKIPIF--
<?php if (!extension_loaded("skyray")) print "skip"; ?>
--FILE--
<?php
use skyray\Reactor;
use skyray\stream\Client;
use skyray\stream\ProtocolInterface;

$server = require_once __DIR__ . '/includes/ServerProcess.php';
register_shutdown_function(function () use ($server) {
    $server->stop();
});

class MyProtocol implements ProtocolInterface
{
    public function connectStream($stream)
    {
        $this->stream = $stream;
    }

    public function streamConnected()
    {
        var_dump('connected');
        $this->stream->write("GET / HTTP/1.1\r\n\r\n");
    }

    public function dataReceived($data)
    {
        echo 'data: ' . trim($data) . PHP_EOL;
    }

    public function streamClosed()
    {
        var_dump('closed');
    }
}


$reactor = new Reactor();

$client = new Client(null);

$stream = $client->connectTCP('127.0.0.1', 2333);
$stream->setProtocol(new MyProtocol());
echo get_class($stream->getProtocol()) . PHP_EOL;
$reactor->attach($stream);
$reactor->run();
?>
--EXPECTF--
string(9) "connected"
MyProtocol
data: HTTP/1.1 404 Not Found
string(6) "closed"
