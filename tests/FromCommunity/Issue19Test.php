<?php

declare(strict_types=1);

namespace OpenAPIValidationTests\FromCommunity;

use GuzzleHttp\Psr7\ServerRequest;
use OpenAPIValidation\PSR7\Exception\ValidationFailed;
use OpenAPIValidation\PSR7\ServerRequestValidator;
use OpenAPIValidation\PSR7\ValidatorBuilder;
use PHPUnit\Framework\TestCase;
use function json_encode;

/**
 * @see https://github.com/thephpleague/openapi-psr7-validator/issues/19
 */
final class Issue19Test extends TestCase
{
    /** @var string $yamlFile */
    private $yamlFile = __DIR__ . '/../stubs/date-times.yaml';
    /** @var ServerRequestValidator $validator */
    private $validator;

    protected function setUp() : void
    {
        parent::setUp();
        $this->validator = (new ValidatorBuilder())->fromYamlFile($this->yamlFile)->getServerRequestValidator();
    }

    public function testInvalidDateTime() : void
    {
        // For regression testing, try a date-time without a time zone (ie. an invalid value)
        $this->expectException(ValidationFailed::class);
        $this->validator->validate($this->makeRequest('2019-10-11T08:03:43'));
    }

    public function testDateTime() : void
    {
        // For regression testing, try the currently allowed date time format
        $this->validator->validate($this->makeRequest('2019-10-11T08:03:43Z'));
        $this->addToAssertionCount(1);
    }

    public function testDateTimeWithMilliseconds() : void
    {
        $this->validator->validate($this->makeRequest('2019-10-11T08:03:43.500Z'));
        $this->addToAssertionCount(1);
    }

    protected function makeRequest(string $dateTimeString) : ServerRequest
    {
        $data['createdAt'] = $dateTimeString;
        $body              = json_encode($data);

        return new ServerRequest(
            'POST',
            'http://localhost:8000/products.create',
            ['Content-Type' => 'application/json'],
            $body
        );
    }
}
