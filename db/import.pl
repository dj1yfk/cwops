#!/usr/bin/perl#

use warnings;
use strict;

# 7,,,K5MA,Jan,,,,,,,,,,,,,,,,,31-Dec-2009,13-Nov-2018,K,MA,SK


print "delete from cwops_members;\n";

while (my $line = <>) {
    next unless ($line =~ /^\d/);

    my @a = split(/,/, $line);
    my $nr = $a[0];
    my $callsign = $a[3];
    my $joined = $a[21];
    my $left = $a[22];
 
    $joined = &dateformat($joined);
    if ($left) {
        $left = &dateformat($left)
    }
    else {
        $left = "20990101";
    }

    print "INSERT into cwops_members (`nr`, `callsign`, `joined`, `left`) VALUES ('$nr', '$callsign', '$joined', '$left');\n";
}


# in 1-Jun-2019 out -> 20190601
sub dateformat {
    my %months = ( "Jan" => "01", "Feb" => "02", "Mar" => "03", "Apr" => "04", "May" => "05", "Jun" => "06", "Jul" => "07", "Aug" => "08", "Sep" => "09", "Oct" => "10", "Nov" => "11", "Dec" => "12" );
    my @d = split(/-/, shift);
    return sprintf("%s%s%02d", $d[2], $months{$d[1]}, $d[0]);
}
