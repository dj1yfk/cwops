#!/usr/bin/perl 

# 2020-02-06 DJ1YFK

# cwops.telegraphy.de receives a monthly update of the member list 
# by N7WY, who puts in a lot of effort to get join/leave dates,
# alternative calls, etc. right.
#
# for the time between the updates, this script checks the official roster
# every day (called by cron) and checks for new members. if a new member
# appears, it creates a command line to insert this member into the database,
# which has to be approved/executed manually
#
# Once a new file form N7WY arrives, all changes done in the meantime are
# discarded because the cwops memberlist will be completely deleted and
# rewritten.


# find highest currently known CWops member from the database
my $maxnr = `echo "select max(nr) from cwops_members where nr!=3588" | mysql -ucwops -pcwops CWops | tail -1`;

my $if = `curl -s http://www.cwops.org/old/roster.html`;
if (!($if =~ /src="(.*)"/)) {
    print "Could not find iframe info in roster.html!";
    exit;
}
# get the actual document from docs.google.com

my $data = `curl -s "$1"`;

$data =~ s/<tr[^>]*>/\n/g;
$data =~ s/<[^>]*>/;/g;

my @arr = split(/\n/, $data);

my $out = "";

my $today = `date +%Y-%m-%d`;
chomp($today);

foreach my $line (@arr) {
    my @a = split(/;/, $line);
    #    ;;1924;;;;;LIFE;;WB0GKH;;952;;Mitch;;Schultz;;K;;WI;;;;;;;;;;1;;
    if ($#a > 18 and $a[2] =~ /^\d+$/ and ($a[7] eq "LIFE" or $a[7] =~ /^\d+$/)) {
        if ($a[19] eq "--") {
            $a[19] = "";
        }
        if ($a[11] > $maxnr and $a[9] ne "VE3INE") {
            $out .= "echo \"insert into cwops_members (\\`nr\\`, \\`callsign\\`, \\`was\\`, \\`joined\\`, \\`left\\`)  VALUES ($a[11], '$a[9]', '$a[19]', '$today', '2099-01-01');\" | mysql -ucwops -pcwops CWops \n";
        }
    }
}

if ($out) {
    print "unset HISTFILE\n";
    print $out;
}
