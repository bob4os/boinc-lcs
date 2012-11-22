#!/usr/bin/perl

#
# A very simple http (onefile) server for boinc-lcs
#

use HTTP::Daemon;
use HTTP::Status;

my $file = '/home/username/bin/BOINC/client_state.xml';
my $port = 8080;

#-----------------------------------------------------------------------

my $daemon = new HTTP::Daemon
	LocalAddr => '0.0.0.0',
	LocalPort => $port;

print "Listener started\n";

while(my $connection = $daemon->accept) {

	while(my $request = $connection->get_request) {

		if($request->method eq "GET") {
			$connection->send_file_response($file);
		}

	}

	$connection->close;
	undef($connection);

}
