<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Jobs\SyncUserFromQywx;
use Queue;

class UserTest extends TestCase
{
    public function testList()
    {
        $this->getJson('/api/users')->assertStatus(401);

        $this->iam('usertest')->getJson('/api/users')->assertStatus(403);

        $this->iam('useradmin')->getJson('/api/users')
            ->assertStatus(200)
            ->assertJson(['status' => 'ok']);
    }

    public function testSync()
    {
        $this->postJson('/api/users/sync')->assertStatus(401);

        $this->iam('usertest')->postJson('/api/users/sync')->assertStatus(403);

        $this->iam('useradmin')->getJson('/api/users/sync')->assertStatus(405);

        $this->iam('useradmin', function () {
            Queue::fake();
        })->postJson('/api/users/sync')
            ->assertStatus(200)
            ->assertJson(['status' => 'ok']);

        Queue::assertPushed(SyncUserFromQywx::class);
    }

    public function testSendMessage()
    {
        $username = 'cscs'; // 在这里改成你的微信 userid 测试

        $this->postJson('/api/users/sendmessage')->assertStatus(401);

        $this->iam('usertest')->postJson('/api/users/sendmessage')->assertStatus(403);

        $this->iam('useradmin')->getJson('/api/users/sendmessage')->assertStatus(405);

        $this->iam('useradmin')
            ->postJson('/api/users/sendmessage', ['username' => 'notExist', 'message' => '测试消息'])
            ->assertStatus(200)
            ->assertJson(['status' => 'error']);

        $this->iam('useradmin')
            ->postJson('/api/users/sendmessage', ['username' => $username, 'message' => '测试消息'])
            ->assertStatus(200)
            ->assertJson(['status' => 'ok']);
    }
}
