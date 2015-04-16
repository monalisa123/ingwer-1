#!/usr/bin/perl -w

use strict;
use warnings;
use Getopt::Long;
use utf8;

my $help;
my $encoding = "ISO-8859-1";
my $version = "1.1";

GetOptions ('help' => \$help, 'encoding=s' => \$encoding);


if ($help || @ARGV == 0) {
	print "
	lexico2xml.pl, Version $version
	
	Verwendung:
		perl lexico2xml.pl [--encoding] [--help] [Datei(en)]
		
	Weitere Informationen:
		perldoc lexico2xml.pl


";
	exit(0);
}

my @infiles = @ARGV;

if ($encoding eq "utf-8" || $encoding eq "utf8" || $encoding eq "UTF8" || $encoding eq "UTF-8") {
	binmode(STDOUT, ":utf8");
}

print "<?xml version=\"1.0\" encoding=\"$encoding\" standalone=\"yes\"?>\n<corpus>\n";
foreach my $infile (@infiles) {
	my $docStart = 1	;
	my %header;
	my $body;
	
	my $in;
	open $in,  "<:encoding($encoding)", $infile  or die;
	
	while(<$in>){ 
		#print $_;
  		if ($_ =~ /^<(.+?)=(.+?)>/) {
  			my $par = $1;
  			my $val = $2;
  			$par =~ s/&/&amp;/;
  			$val =~ s/&/&amp;/;
  			$val =~ s/</&lt;/;
  			$val =~ s/>/&gt;/;
  			
  			if ($docStart == 0) {
  			
  				$docStart = 1;
  				
  				printDocument(\%header, \$body);
  				
  				%header = ();
  				$body = "";
  			}
  			$header{$par} = $val;
  		} elsif ($_ =~ /^[^<]/) {
  			if ($_ !~ /^\s+$/) {
  				if ($docStart == 1) { $docStart = 0; }
  			}
  			$body .= $_;
  		}
  	}
  	
  	printDocument(\%header, \$body);
}
print "</corpus>";


sub printDocument {
	my %header = %{(shift)};
	my $body = ${(shift)};
	
	#$body =~ s/^([^\.]+\.)/<title>$1<\/title>\n<text>/;
	#$body =~ s/^\s+(.+)\n\n/<title>$1<\/title>\n<text>/;
	$body =~ s/^\s+//;
	$body =~ s/\s+$//;
	$body =~ s/&/&amp;/g;
	$body =~ s/</&lt;/g;
	$body =~ s/>/&gt;/g;
	
	my @body = split(/\n\s*\n/,$body);
	
	# Print Document
	print "<document>\n\t<header>\n";
	my @fields = sort(keys(%header));
	foreach my $f (@fields) {
		print "\t\t<$f>".$header{$f}."</$f>\n";
	}
	print "\t</header>\n\t<body>\n";
	if (@body == 1) {
		print "\t\t<text>\n".$body[0]."\n\t\t<\/text>\n";
	} elsif (@body == 2) {
		print "\t\t<title>".$body[0]."<\/title>\n\t\t<text>\n".$body[1]."\n\t\t<\/text>\n";
	} elsif (@body == 3) {
		print "\t\t<title>".$body[0]."<\/title>\n\t\t<subtitle>".$body[1]."\t\t<\/subtitle>\n\t\t<text>\n".$body[2]."\n\t\t<\/text>\n";
	} else {
		print "\t\t<title>".$body[0]."<\/title>\n\t\t<subtitle>".$body[1]."\t\t<\/subtitle>\n";
		@body = reverse(@body);
		pop(@body);
		pop(@body);
		print "\t\t<text>\n".join("\n\n",@body)."\n\t\t<\/text>\n";
	}
	print "\n\t</body>\n</document>\n";
	#print "\t</header>\n<body>\n$body\n</text>\n</body>\n</document>\n";
}


=pod

=head1 NAME

B<lexico2xml.pl> - Script zur Konvertierung vom Lexico-3-Format nach XML.


=head1 SYNOPSIS

B<lexico2xml.pl> [B<--encoding> I<Code>] [B<--help>] [I<Datei(en)>]


=head1 DESCRIPTION

Das Script konvertiert eine oder mehrere Dateien im Format
Lexico 3 nach XML, wobei alle Dokumente mit einem
Tag C<E<lt>corpusE<gt>> umfasst werden.

=head1 OPTIONS

=over 4

=item [B<--encoding> I<Code>]

Encoding der Input-Datei (Voreinstellung: ISO-8859-1).

=item [B<--help>]

Zeigt die Hilfe.

=back


=head1 REQUIRES

Perl 5, Getopt::Long

=head1 AUTHOR

Noah Bubenhofer E<lt>bubenhofer@semtracks.comE<gt>

=cut
