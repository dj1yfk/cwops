#!/usr/bin/perl#

use warnings;
use strict;

# 7,,,K5MA,Jan,,,,,,,,,,,,,,,,,31-Dec-2009,13-Nov-2018,K,MA,SK


print "delete from cwops_members;\n";

while (my $line = <>) {
    next unless ($line =~ /^\d/);

    my @a = split(/,/, $line);
    my $nr = $a[0];
    my $callsign = $a[1];
    my $joined = $a[3];
    my $left = $a[4];
    my $state = ($a[5] eq "K" or $a[5] eq "KL7" or $a[5] eq "KL" or $a[5] eq "KH6") ? $a[6] : "";
 
    $joined = &dateformat($joined);
    if ($left) {
        $left = &dateformat($left)
    }
    else {
        $left = "20990101";
    }

    print "INSERT into cwops_members (`nr`, `callsign`, `joined`, `left`, `was`) VALUES ('$nr', '$callsign', '$joined', '$left', '$state');\n";

    # special calls for crown jubilee
    my $nc = 0;
    if ($callsign =~ /^VA(.*)/) {
        $nc = "VG$1";
    }
    elsif ($callsign =~ /^VE(.*)/) {
        $nc = "VX$1";
    }
    elsif ($callsign =~ /^VO(.*)/) {
        $nc = "XJ$1";
    }
    elsif ($callsign =~ /^VY(.*)/) {
        $nc = "XK$1";
    }
    # Gx = GQ
    elsif ($callsign =~ /^([2GM])[A-Z]?(.*)/) {
        $nc = "$1Q$2";
    }

    if ($nc and $left eq "20990101") {
        print "INSERT into cwops_members (`nr`, `callsign`, `joined`, `left`, `was`) VALUES ('$nr', '$nc', '20220514', '20990101', '$state');\n";
    }


}


# in 1-Jun-19 out -> 20190601
sub dateformat {
    my %months = ( "Jan" => "01", "Feb" => "02", "Mar" => "03", "Apr" => "04", "May" => "05", "Jun" => "06", "Jul" => "07", "Aug" => "08", "Sep" => "09", "Oct" => "10", "Nov" => "11", "Dec" => "12" );
    my @d = split(/-/, shift);
    return sprintf("20%s%s%02d", $d[2], $months{$d[1]}, $d[0]);
}
