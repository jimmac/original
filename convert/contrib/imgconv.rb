#!/usr/bin/ruby

require 'fileutils'

if ARGV.size < 2
	puts "Usage: #$0 source_images gallery_name"
	exit 1
end

class Gallery

	CONF = {}
	CONF[:thumbs] = { :target => 'thumbs', :geom => '120x120', :quality => '60' }
	CONF[:lq] = { :target => 'lq', :geom => '640x480', :quality => '90' }
	CONF[:mq] = { :target => 'mq', :geom => '800x600', :quality => '80' }

	def initialize(source_dir = '.', gallery = 'web-gallery')
		@source_dir = source_dir
		@gallery = gallery
	end

	def setup_dirs
		%w(thumbs lq mq hq zip comments).each do |dir|
			target = File.join(@gallery, dir)

			if File.exists? target
				warn "Skipping creation of, #{target}, already exists."
				next
			end

			FileUtils.mkdir_p target
		end
	end

	def setup_access
		ht = File.join(@gallery, ".htaccess")

		open(ht, 'w') do |fp|
			fp.puts "<Files info.txt>"
			fp.puts "  deny from all"
			fp.puts "</Files>"
		end
	end

	def create_comment(num)
		comment_file = File.join(@gallery, 'comments', "#{num}.txt")

		open(comment_file, 'w') do |fp|
			fp.puts "<span>image #{num}</span>"
		end
	end

	def convert_image(conf, src, num)
		target = File.join(@gallery, conf[:target], "img-#{num}.jpg")

		cmd = "convert -geometry #{conf[:geom]} -unsharp 1x5 "
		cmd += "-quality #{conf[:quality]} #{src} #{target}"

		system cmd
	end

	def build_zip_files
		puts "Building zip files."
		system("zip -R #@gallery/zip/mq.zip  #@gallery/mq/*.jpg");
		system("zip -R #@gallery/zip/hq.zip  #@gallery/hq/*.jpg");
	end

	def create
		setup_dirs
		setup_access

		num = 1

		Dir["#@source_dir/*.jpg"].sort.each do |src|
			puts "Working on: #{src}"

			FileUtils.cp(src, File.join(@gallery, 'hq', "img-#{num}.jpg"))

			convert_image(CONF[:thumbs], src, num)
			convert_image(CONF[:lq], src, num)
			convert_image(CONF[:mq], src, num)

			create_comment(num)

			num += 1
		end

		build_zip_files
	end
end

gal = Gallery.new ARGV[0], ARGV[1]
gal.create

