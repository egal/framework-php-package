<?php

namespace Egal\Tests\Core;

use Egal\Core\Communication\Request;
use Egal\Core\Communication\Response;
use Egal\Core\Exceptions\ImpossibilityDeterminingStatusOfResponseException;
use Egal\Core\Messages\ActionErrorMessage;
use Egal\Core\Messages\ActionResultMessage;
use Egal\Core\Messages\StartProcessingMessage;
use Egal\Tests\PHPUnitUtil;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class CommunicationRequestTest extends TestCase
{

    public function dataProviderSetResponseStatusCode()
    {
        return [
            //  SPM?,   AEM?,   AEM C,  ARM?,   E C,    E E
            [   false,  false,  null,   false,  500,    false   ],
            [   true,   false,  null,   false,  500,    false   ],
            [   true,   true,   500,    false,  500,    false   ],
            [   true,   true,   401,    false,  401,    false   ],
            [   true,   false,  null,   true,   200,    false   ],
            [   false,  true,   401,    false,  null,   true    ],
            [   false,  true,   401,    true,   null,   true    ],
            [   false,  true,   500,    true,   null,   true    ],
            [   false,  true,   null,   false,  null,   true    ],
            [   false,  false,  null,   true,   null,   true    ],
        ];
    }

    /**
     * @dataProvider dataProviderSetResponseStatusCode
     */
    public function testSetResponseStatusCode(
        bool $startProcessingMessageExists,
        bool $actionErrorMessageExists,
        ?int $actionErrorMessageCode,
        bool $actionResultMessageExists,
        ?int $expectedCode,
        bool $expectDefinitionOfResponseStatusException
    ) {
        $request = new Request('service', 'model', 'action', []);
        $response = new Response();

        if ($startProcessingMessageExists) {
            $startProcessingMessage = m::mock(StartProcessingMessage::class);
            $response->setStartProcessingMessage($startProcessingMessage);
        }

        if ($actionErrorMessageExists) {
            $actionErrorMessage = m::mock(ActionErrorMessage::class);
            $actionErrorMessage->shouldReceive('getMessage')->andReturn('foo');
            $actionErrorMessage->shouldReceive('getCode')->andReturn($actionErrorMessageCode);
            $response->setActionErrorMessage($actionErrorMessage);
        }

        if ($actionResultMessageExists) {
            $actionResultMessage = m::mock(ActionResultMessage::class);
            $response->setActionResultMessage($actionResultMessage);
        }

        if ($expectDefinitionOfResponseStatusException) {
            $this->expectException(ImpossibilityDeterminingStatusOfResponseException::class);
        }

        PHPUnitUtil::setProperty($request, 'response', $response);

        PHPUnitUtil::callMethod($request, 'setResponseStatusCode');

        if ($expectedCode) {
            $this->assertEquals($expectedCode, $request->getResponse()->getStatusCode());
        }
    }

}
