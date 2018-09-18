<?php
declare(strict_types = 1);

namespace Soliant\SimpleFM\Repository\Builder\Type;

use Psr\Http\Message\StreamInterface;
use Soliant\SimpleFM\Client\ClientInterface;

final class StreamProxy implements StreamInterface
{
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var string
     */
    private $url;

    /**
     * @var StreamInterface
     */
    private $wrappedStream;

    public function __construct(ClientInterface $client, string $url)
    {
        $this->client = $client;
        $this->url = $url;
    }

    public function __toString() : string
    {
        return $this->getWrappedStream()->__toString();
    }

    public function close()
    {
        $this->getWrappedStream()->close();
    }

    public function detach()
    {
        return $this->getWrappedStream()->detach();
    }

    public function eof() : bool
    {
        return $this->getWrappedStream()->eof();
    }

    public function getContents() : string
    {
        return $this->getWrappedStream()->getContents();
    }

    public function getMetadata($key = null)
    {
        return $this->getWrappedStream()->getMetadata($key);
    }

    public function getSize()
    {
        return $this->getWrappedStream()->getSize();
    }

    public function isReadable() : bool
    {
        return $this->getWrappedStream()->isReadable();
    }

    public function isSeekable() : bool
    {
        return $this->getWrappedStream()->isSeekable();
    }

    public function isWritable() : bool
    {
        return $this->getWrappedStream()->isWritable();
    }

    public function read($length) : string
    {
        return $this->getWrappedStream()->read($length);
    }

    public function rewind()
    {
        $this->getWrappedStream()->rewind();
    }

    public function seek($offset, $whence = SEEK_SET)
    {
        $this->getWrappedStream()->seek($offset, $whence);
    }

    public function tell() : int
    {
        return $this->getWrappedStream()->tell();
    }

    public function write($string) : int
    {
        return $this->getWrappedStream()->write($string);
    }

    private function getWrappedStream() : StreamInterface
    {
        if (null !== $this->wrappedStream) {
            return $this->wrappedStream;
        }

        return $this->wrappedStream = $this->client->getContainerData($this->url);
    }
}
