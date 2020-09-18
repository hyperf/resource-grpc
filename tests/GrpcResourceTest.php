<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace HyperfTest\ResourceGrpc;

use HyperfTest\ResourceGrpc\Stubs\Grpc\AllReply;
use HyperfTest\ResourceGrpc\Stubs\Grpc\HiReply;
use HyperfTest\ResourceGrpc\Stubs\Grpc\HiUser;
use HyperfTest\ResourceGrpc\Stubs\Models\Reply;
use HyperfTest\ResourceGrpc\Stubs\Models\User;
use HyperfTest\ResourceGrpc\Stubs\Resources\AllReplyResource;
use HyperfTest\ResourceGrpc\Stubs\Resources\HiReplyResource;
use HyperfTest\ResourceGrpc\Stubs\Resources\HiUserResource;

/**
 * @internal
 * @coversNothing
 */
class GrpcResourceTest extends \PHPUnit\Framework\TestCase
{
    public function testResourceToMessage()
    {
        /** @var HiUser $msg */
        $msg = HiUserResource::make(new User(['name' => 'nfangxu', 'sex' => 1]))->toMessage();

        $this->assertSame('{"name":"nfangxu","sex":1}', $msg->serializeToJsonString());
        $this->assertSame(HiUser::class, get_class($msg));
    }

    public function testCollectionToMessage()
    {
        $collection = collect([
            new Reply([
                'message' => 'foo',
                'user' => new User(['name' => 'nfangxu', 'sex' => 1]),
            ]),
            new Reply([
                'message' => 'bar',
                'user' => new User(['name' => 'nfangxu', 'sex' => 1]),
            ]),
        ]);

        $msg = HiReplyResource::collection($collection)->toMessage();

        $this->assertTrue(is_array($msg));

        $this->assertCount(2, $msg);

        foreach ($msg as $value) {
            $this->assertSame(HiReply::class, get_class($value));
            $this->assertSame(HiUser::class, get_class($value->getUser()));
        }
    }

    public function testResourceMayUserOtherResource()
    {
        $msg = HiReplyResource::make(new Reply([
            'message' => 'foo',
            'user' => new User(['name' => 'nfangxu', 'sex' => 1]),
        ]))->toMessage();

        $this->assertSame('{"message":"foo","user":{"name":"nfangxu","sex":1}}', $msg->serializeToJsonString());
        $this->assertSame(HiReply::class, get_class($msg));
        $this->assertSame(HiUser::class, get_class($msg->getUser()));
    }

    public function testResourceMayUseCollection()
    {
        /** @var AllReply $msg */
        $msg = AllReplyResource::make(new Reply([
            'message' => 'foo',
            'users' => HiUserResource::collection([
                new User(['name' => 'nfangxu-01', 'sex' => 1]),
                new User(['name' => 'nfangxu-02', 'sex' => 1]),
            ]),
        ]))->toMessage();

        $this->assertSame(
            '{"message":"foo","users":[{"name":"nfangxu-01","sex":1},{"name":"nfangxu-02","sex":1}]}',
            $msg->serializeToJsonString()
        );

        $this->assertSame(AllReply::class, get_class($msg));

        foreach ($msg->getUsers() as $user) {
            $this->assertSame(HiUser::class, get_class($user));
        }
    }
}
