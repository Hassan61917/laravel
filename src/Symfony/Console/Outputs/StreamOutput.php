<?php

namespace Src\Symfony\Console\Outputs;

use InvalidArgumentException;

class StreamOutput extends Output
{
    protected $stream;
    public function __construct(
        $stream,
        bool $decorated = false
    ) {
        $this->setStream($stream);
        parent::__construct($decorated);
    }
    public function setStream($stream): void
    {
        if (!is_resource($stream) || get_resource_type($stream) != "stream") {
            throw new InvalidArgumentException('The StreamOutput class needs a stream as its first argument.');
        }

        $this->stream = $stream;
    }
    public function getStream()
    {
        return $this->stream;
    }
    protected function doWrite(string $message, bool $newline): void
    {
        if ($newline) {
            $message .= "\n";
        }

        fwrite($this->stream, $message);

        fflush($this->stream);
    }
}
