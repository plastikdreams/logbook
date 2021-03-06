<?php

class LogBook_Log_Test extends \WP_UnitTestCase
{
	public function test_log_object()
	{
		$log = new LogBook\Log();
		$this->assertTrue( is_a( $log->get_log(), 'WP_Error' ) );
	}

	public function test_log_object_should_return_stdclass()
	{
		$test_flag = false;

		add_action( 'test_hook', function() use ( &$test_flag ) {
			$test_flag = true;

			$log_object = new LogBook\Log();
			$log_object->set_title( 'this is log' );
			$log = $log_object->get_log();

			$this->assertTrue( is_a( $log, 'stdClass' ) );
			$this->assertSame( 'this is log', $log->title );
			$this->assertSame( '', $log->content );
			$this->assertSame( 0, $log->user );
			$this->assertSame( 'General', $log->meta['label'] );
			$this->assertSame( null, $log->meta['log_level'] );
			$this->assertSame( 'test_hook', $log->meta['hook'] );
			$this->assertSame( false, $log->meta['is_cli'] );

			$log_object->update_meta( 'foo', 'bar' );
			$this->assertSame( 'bar', $log_object->get_log()->meta['foo'] );
			$log_object->delete_meta( 'foo' );
			$this->assertTrue( empty( $log_object->get_log()->meta['foo'] ) );
		} );

		do_action( 'test_hook' );
		$this->assertTrue( $test_flag );
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function test_log_object_should_return_stdclass_with_some_option()
	{
		$user_id = $this->set_current_user( 'administrator' );
		define( 'WP_CLI', true );

		$test_flag = false;

		add_action( 'test_hook', function() use ( &$test_flag, $user_id ) {
			$test_flag = true;

			$log_object = new LogBook\Log();
			$log_object->set_title( 'this is log' );
			$log_object->add_content( 'hello', 'world' );
			$log_object->set_label( 'Post' );
			$log_object->set_log_level( 'debug' );
			$log = $log_object->get_log();

			$this->assertTrue( is_a( $log, 'stdClass' ) );
			$this->assertSame( 'this is log', $log->title );
//			$this->assertSame( 'hello', $log->content );
			$this->assertSame( $user_id, $log->user );
			$this->assertSame( 'Post', $log->meta['label'] );
			$this->assertSame( 'debug', $log->meta['log_level'] );
			$this->assertSame( 'test_hook', $log->meta['hook'] );
			$this->assertSame( true, $log->meta['is_cli'] );
		} );

		do_action( 'test_hook' );
		$this->assertTrue( $test_flag );
	}

	/**
	 * Add user and set the user as current user.
	 *
	 * @param  string $role administrator, editor, author, contributor ...
	 * @return int The user ID
	 */
	private function set_current_user( $role )
	{
		$user = $this->factory()->user->create_and_get( array(
			'role' => $role,
		) );

		wp_set_current_user( $user->ID, $user->user_login );

		return $user->ID;
	}
}
