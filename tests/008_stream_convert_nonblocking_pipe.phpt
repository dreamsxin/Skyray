--TEST--
Test for skyray\stream\Stream() connvert to non-blocking (pipe)
--SKIPIF--
<?php if (!extension_loaded("skyray")) print "skip"; ?>
--FILE--
<?php
use skyray\Reactor;
use skyray\stream\Client;
use skyray\stream\ProtocolInterface;

class MyProtocol implements ProtocolInterface
{
    public function connectStream($stream)
    {
        $this->stream = $stream;
    }

    public function streamConnected()
    {
        var_dump('connected');
        //$this->stream->write("GET / HTTP/1.1\r\n\r\n");
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

$streams = $client->createPipe(true);

$streams[0]->setProtocol(new MyProtocol());

$reactor->attach($streams[0]);

$streams[1]->write("hello world");
$streams[1]->close();

$reactor->run();
?>
--EXPECTF--
string(9) "connected"
data: hello world
string(6) "closed"
