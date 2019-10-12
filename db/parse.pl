#!/usr/bin/perl

use strict;
use warnings;

use Data::Dumper;
use JSON;

# Parse OK1RR's country resolution file Exception list
#
# =3D2AA =3D2HI =3D2HK =3D2ID =3D2KZ =3D2MU =3D2SH|Rotuma|OC|-12|12.4993S|177.0474E|56|32||R|1995/11/04-1995/11/07=460
# =3D2AA =3D2HI =3D2HK =3D2ID =3D2KZ =3D2MU =3D2SH|Rotuma|OC|-12|12.4993S|177.0474E|56|32|R|1995/11/04-1995/11/07=460
# Fields: 
#  0 => Callsigns
#  1 => Name DXCC
#  2 => Continent
#  3 => UTC offset
#  4, 5 => Lat/Lon
#  6 => ITU
#  7 => WAZ
#  8 => empty? (removed in new files)
#  8 => R?
#  9 => date range = DXCC ADIF number

# output format is JSON
# format example:
# "FO/UT6UD":[{"start":"20130714","stop":20130722,"waz":"32","adif":"508"},{"adif":"509","waz":"31","stop":20130713,"start":"20130707"}]
#
# The JSON can the be converted with "j2p.php" to PHP's own serialization format.
#
# hash of arrays.
# callsign => array of hashes with WAZ, DXCC, Start, Stop
my %h = ();

while (my $line = <>) {
    chomp($line);

    next if ($line =~ /no DXCC/);

    my @a = split(/\|/, $line);
    $a[0] =~ s/=//g;
    my @c = split(/\s+/, $a[0]);

    foreach (@c) {
        # check date range if any. it may be in the following formats:
        my @d = split(/=/, $a[9]);

        my ($start, $stop) = qw/20100101 20990101/;
        if ($d[0]) {
            $d[0] =~ s/\///g;

            # convert single day (20190101) into range (20190101-20190101)
            unless ($d[0] =~ /\-/) {
                $d[0] = $d[0]."-".$d[0];
            }

            ($start, $stop) = split(/\-/, $d[0]);

            if ($stop ne "" and $stop < "20100101") {
                next;
            }

            if ($stop eq "") {
                $stop = "20990101";
            }

            if ($start eq "") {
                $start = "20100101";
            }

        }

        push @{$h{$_}}, { 'adif' => $d[1], 'waz' => $a[7], 'itu' => $a[6], 'start' => $start, 'stop' => $stop };
    }
}

print encode_json(\%h);
